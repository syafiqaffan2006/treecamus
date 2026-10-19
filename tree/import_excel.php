<?php
// 1. Sambungan Database & Fungsi Token
include "../connection/connection.php";
include "../function/function.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fungsi helper untuk semak keunikan token di dalam database (Hanya dipanggil semasa INSERT sebenar)
function getUniqueToken($dbh, $table, $column) {
    do {
        $token = getToken(36); 
        $stmt = $dbh->prepare("SELECT COUNT(*) FROM `$table` WHERE `$column` = ?");
        $stmt->execute([$token]);
        $exists = $stmt->fetchColumn();
    } while ($exists > 0);
    return $token;
}

$message = "";
$previewData = [];
$showPreview = false;

// ===================================================
// AKSI 1: KLIK BUTANG "SEMAK & PREVIEW DATA"
// ===================================================
if (isset($_POST['upload_csv'])) {
    if ($_FILES['csv_file']['error'] == 0) {
        $filename = $_FILES['csv_file']['tmp_name'];
        $file = fopen($filename, "r");
        
        // Lompat baris pertama (header)
        fgetcsv($file);
        
        // OPTIMASI: Tarik semua data dari DB masuk ke memori PHP (In-Memory Cache) untuk elak N+1 Query
        $familyCache = [];
        $speciesCache = [];
        
        $families = $dbh->query("SELECT familyID, familytitle FROM family")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($families as $f) {
            $familyCache[strtolower(trim($f['familytitle']))] = $f['familyID'];
        }
        
        $species = $dbh->query("SELECT familyID, speciestitle, specieslocalname FROM species")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($species as $s) {
            $key = $s['familyID'] . '|' . strtolower(trim($s['speciestitle'])) . '|' . strtolower(trim($s['specieslocalname']));
            $speciesCache[$key] = true;
        }
        
        $rowNum = 2;
        while (($row = fgetcsv($file, 1000, ",")) !== FALSE) {
            $familyTitle      = trim($row[0]);
            $speciesTitle     = trim($row[1]);
            $speciesLocalName = trim($row[2]);
            
            if (empty($familyTitle) && empty($speciesTitle)) continue;
            
            $status = "INSERT"; 
            $familyKey = strtolower($familyTitle);
            $familyID = isset($familyCache[$familyKey]) ? $familyCache[$familyKey] : null;
            
            $familyTokenToDisplay = "-";
            $speciesTokenToDisplay = "-";
            
            if ($familyID !== null) {
                // Family wujud, semak species dalam In-Memory Cache
                $speciesKey = $familyID . '|' . strtolower($speciesTitle) . '|' . strtolower($speciesLocalName);
                
                if (isset($speciesCache[$speciesKey])) {
                    $status = "NO INSERT";
                } else {
                    $speciesTokenToDisplay = getToken(36);
                }
            } else {
                // Family baru, kedua-dua token dijana baru
                $familyTokenToDisplay = getToken(36);
                $speciesTokenToDisplay = getToken(36);
            }
            
            $previewData[] = [
                'row' => $rowNum,
                'familytitle' => $familyTitle,
                'familytoken' => $familyTokenToDisplay,
                'speciestitle' => $speciesTitle,
                'speciestoken' => $speciesTokenToDisplay,
                'specieslocalname' => $speciesLocalName,
                'status' => $status
            ];
            $rowNum++;
        }
        fclose($file);
        
        $_SESSION['import_data'] = $previewData;
        $showPreview = true;
    } else {
        $message = "<div class='alert alert-danger'>Ralat: Gagal memuat naik fail Excel/CSV.</div>";
    }
}

// ===================================================
// AKSI 2: KLIK BUTANG "SAHKAN & SIMPAN REKOD BARU"
// ===================================================
if (isset($_POST['confirm_upsert'])) {
    if (!empty($_SESSION['import_data'])) {
        $dataToProcess = $_SESSION['import_data'];
        
        $insertedCount = 0;
        $skippedCount = 0;
        
        // OPTIMASI: Mulakan Database Transaction (Bungkus semua query)
        $dbh->beginTransaction();
        
        try {
            $stmtCheckFamily  = $dbh->prepare("SELECT familyID FROM family WHERE familytitle = ?");
            $stmtInsertFamily = $dbh->prepare("INSERT INTO family (familytitle, is_display, familytoken) VALUES (?, 1, ?)");
            $stmtInsertSpecies = $dbh->prepare("INSERT INTO species (familyID, speciestitle, specieslocalname, is_display, speciestoken) VALUES (?, ?, ?, 1, ?)");
            
            // Simpan cache familyID baru yang dicipta semasa loop berjalan
            $localFamilyCache = [];
            
            foreach ($dataToProcess as $data) {
                if ($data['status'] == 'NO INSERT') {
                    $skippedCount++;
                    continue; 
                }

                $familyTitle = $data['familytitle'];
                $speciesTitle = $data['speciestitle'];
                $speciesLocalName = $data['specieslocalname'];
                $familyKey = strtolower($familyTitle);

                // 1. Logik Pengurusan ID Family
                if (isset($localFamilyCache[$familyKey])) {
                    $familyID = $localFamilyCache[$familyKey];
                } else {
                    $stmtCheckFamily->execute([$familyTitle]);
                    $familyRow = $stmtCheckFamily->fetch(PDO::FETCH_ASSOC);
                    
                    if ($familyRow) {
                        $familyID = $familyRow['familyID'];
                    } else {
                        // Jika familyToken dalam preview adalah '-', jana baru demi keselamatan
                        $fToken = ($data['familytoken'] !== '-') ? $data['familytoken'] : getUniqueToken($dbh, 'family', 'familytoken');
                        $stmtInsertFamily->execute([$familyTitle, $fToken]);
                        $familyID = $dbh->lastInsertId();
                    }
                    // Simpan dalam cache lokal loop
                    $localFamilyCache[$familyKey] = $familyID;
                }
                
                // 2. Insert Species Baru
                $sToken = ($data['speciestoken'] !== '-') ? $data['speciestoken'] : getUniqueToken($dbh, 'species', 'speciestoken');
                $stmtInsertSpecies->execute([$familyID, $speciesTitle, $speciesLocalName, $sToken]);
                $insertedCount++;
            }
            
            // Sahkan semua kemasukan data jika tiada ralat berlaku
            $dbh->commit();
            $message = "<div class='alert alert-success fw-bold'>Proses Selesai (Transaction Committed)!<br>✓ $insertedCount Rekod Baru berjaya dimasukkan (INSERT).<br>➜ $skippedCount Rekod Sedia Ada diabaikan sepenuhnya (NO INSERT).</div>";
            unset($_SESSION['import_data']); 
            
        } catch (Exception $e) {
            // Jika ada satu sahaja error, batalkan semua perubahan! Database kekal selamat.
            $dbh->rollBack();
            $message = "<div class='alert alert-danger fw-bold'>Proses Gagal (Transaction Rolled Back)!<br>Ralat Database dikesan: " . $e->getMessage() . "</div>";
        }
    } else {
        $message = "<div class='alert alert-warning'>Tiada data untuk diproses. Sila upload fail semula.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>UGC Tree Camus - Teroptimum Portal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { padding: 30px; background-color: #f4f6f9; }
        .card { box-shadow: 0 4px 12px rgba(0,0,0,0.08); border: none; }
        .status-new { color: #155724; background-color: #d4edda; font-weight: bold; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; }
        .status-duplicate { color: #721c24; background-color: #f8d7da; font-weight: bold; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; }
        td code { font-size: 0.8rem; font-family: monospace; color: #d63384; word-break: break-all; }
        .text-dash { color: #ccc; font-weight: bold; }
    </style>
</head>
<body>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-success fw-bold">Tree Camus - Data Importer (High-Performance)</h2>
        <a href="" class="btn btn-outline-secondary btn-sm">Reset Halaman</a>
    </div>
    
    <?php echo $message; ?>

    <!-- SEKSYEN 1: FORM MUAT NAIK -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white fw-bold">1. Muat Naik Fail Excel (.CSV)</div>
        <div class="card-body">
            <p class="text-muted text-sm mb-3">*Versi ini dilengkapi dengan <b>In-Memory Caching</b> (laju) dan <b>Database Transactions</b> (selamat daripada rasuah data).</p>
            <form action="" method="POST" enctype="multipart/form-data" class="row g-3 align-items-center">
                <div class="col-md-5">
                    <input type="file" name="csv_file" class="form-control" accept=".csv" required>
                </div>
                <div class="col-md-4">
                    <button type="submit" name="upload_csv" class="btn btn-dark px-4">Semak & Preview Data</button>
                </div>
            </form>
        </div>
    </div>

    <!-- SEKSYEN 2: PREVIEW JADUAL -->
    <?php if ($showPreview): ?>
    <div class="card">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
            <span class="fw-bold text-warning">2. Hasil Semakan Data & Pratonton Token Baharu</span>
            
            <form action="" method="POST" onsubmit="return confirm('Sahkan transaksi pangkalan data? Hanya status INSERT akan disimpan.');">
                <button type="submit" name="confirm_upsert" class="btn btn-warning fw-bold px-4 shadow-sm">Sahkan & Simpan Rekod Baru</button>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                <table class="table table-hover table-bordered align-middle table-sm">
                    <thead class="table-secondary sticky-top">
                        <tr>
                            <th width="50" class="text-center">Baris</th>
                            <th>Family Title</th>
                            <th>Family Token (Untuk INSERT Sahaja)</th>
                            <th>Species Title (Nama Saintifik)</th>
                            <th>Species Token (Untuk INSERT Sahaja)</th>
                            <th>Species Local Name</th>
                            <th width="150" class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($previewData as $row): ?>
                        <tr>
                            <td class="text-center fw-bold text-muted"><?php echo $row['row']; ?></td>
                            <td class="fw-bold"><?php echo htmlspecialchars($row['familytitle']); ?></td>
                            <td>
                                <?php echo ($row['familytoken'] !== '-') ? '<code>'.$row['familytoken'].'</code>' : '<span class="text-dash">-</span>'; ?>
                            </td>
                            <td><i><?php echo htmlspecialchars($row['speciestitle']); ?></i></td>
                            <td>
                                <?php echo ($row['speciestoken'] !== '-') ? '<code>'.$row['speciestoken'].'</code>' : '<span class="text-dash">-</span>'; ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['specieslocalname']); ?></td>
                            <td class="text-center">
                                <?php if ($row['status'] == 'INSERT'): ?>
                                    <span class="status-new">🆕 INSERT</span>
                                <?php else: ?>
                                    <span class="status-duplicate">🛑 NO INSERT</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

</body>
</html>