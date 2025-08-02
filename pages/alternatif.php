<?php
session_start();
include('../config/config.php'); // Koneksi ke database

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Alternatif</title>
    <link rel="stylesheet" href="../assets/css/style.css?v2">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.min.css">
    <style>
    .notif {
        display: none;
        position: fixed;
        top: 20px;
        right: 20px;
        background: #ff4d00;
        color: white;
        padding: 10px 20px;
        border-radius: 5px;
        z-index: 1000;
    }

    .notif.error {
        background: rgb(255, 17, 0);
    }
    </style>
</head>

<body>
    <?php include('../includes/navbar.php'); ?>

    <div class="alternatif">
        <h1>ALTERNATIF</h1>
        <hr>
        <button class="alternatif-button" onclick="openPopup()">Tambah Alternatif</button>

        <!-- Notifikasi -->
        <div id="notif" class="notif"></div>

        <!-- Popup Form -->
        <div class="popup-alternatif-main">
            <div class="popup-alternatif" id="popupForm">
                <span class="close-btn" onclick="closePopup()">×</span>
                <h2 id="popupTitle">Tambah Alternatif</h2>
                <form method="POST" id="alternatifForm">
                    <input type="hidden" id="id" name="id">

                    <label for="kode">Kode:</label>
                    <input type="text" id="kode" name="kode" required>

                    <label for="alternatif">Alternatif:</label>
                    <input type="text" id="alternatif" name="alternatif" required>

                    <label for="posisi">Posisi:</label>
                    <select id="posisi" name="posisi">
                        <option value="spiker">Spiker</option>
                    </select>


                    <button type="submit">Simpan</button>
                </form>
            </div>
        </div>

        <!-- Tabel Data Alternatif -->
        <table id="dataAlternatif">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Alternatif</th>
                    <th>Posisi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT * FROM alternatif";
                $result = $conn->query($query);
                $no = 1;

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>" . $no++ . "</td>
                                <td>" . htmlspecialchars($row['kode']) . "</td>
                                <td>" . htmlspecialchars($row['alternatif']) . "</td>
                                <td>" . htmlspecialchars($row['posisi']) . "</td>
                                <td>
                                    <a href='javascript:void(0);' class='tombol-aksi' onclick=\"editAlternatif('" . $row['id'] . "', '" . $row['kode'] . "', '" . $row['alternatif'] . "', '" . $row['posisi'] . "')\" title='Edit'>
                                        ✏️
                                    </a>
                                    <a href='javascript:void(0);' class='tombol-aksi' onclick=\"hapusAlternatif('" . $row['id'] . "')\" title='Hapus'>
                                        ❌
                                    </a>
                                </td>
                            </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>Belum ada data alternatif.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script>
    function showNotification(message, isError = false) {
        let notif = $("#notif");
        notif.text(message).removeClass("error").fadeIn();

        if (isError) notif.addClass("error");

        setTimeout(() => {
            notif.fadeOut();
        }, 3000);
    }

    $(document).ready(function() {
        $.fn.dataTable.ext.errMode = 'none';
        $('#dataAlternatif').DataTable({
            language: {
                lengthMenu: "Tampilkan _MENU_ data per halaman",
                search: "Cari Data:",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Tidak ada data yang tersedia",
                infoFiltered: "(disaring dari _MAX_ total data)",
                paginate: {
                    first: "Awal",
                    last: "Akhir",
                    next: "➡",
                    previous: "⬅"
                }
            },
            pageLength: 10
        });

        $("#alternatifForm").submit(function(event) {
            event.preventDefault();
            let formData = $(this).serialize();
            $.post("../function/simpan_alternatif.php", formData, function(response) {
                showNotification(response);
                setTimeout(() => location.reload(), 1000);
            }).fail(function() {
                showNotification("Terjadi kesalahan, coba lagi!", true);
            });
        });
    });

    function openPopup() {
        $("#popupTitle").text("Tambah Alternatif");
        $("#id").val("");
        $("#kode").val("");
        $("#alternatif").val("");
        $("#posisi").val("spiker");
        $("#popupForm").show();
    }

    function editAlternatif(id, kode, alternatif, posisi) {
        $("#popupTitle").text("Edit Alternatif");
        $("#id").val(id);
        $("#kode").val(kode);
        $("#alternatif").val(alternatif);
        $("#posisi").val(posisi);
        $("#popupForm").show();
    }

    function closePopup() {
        $("#popupForm").hide();
    }

    function hapusAlternatif(id) {
        if (confirm("Apakah Anda yakin ingin menghapus?")) {
            $.post("../function/hapus_alternatif.php", {
                id: id
            }, function(response) {
                showNotification(response);
                setTimeout(() => location.reload(), 1000);
            }).fail(function() {
                showNotification("Gagal menghapus data!", true);
            });
        }
    }
    </script>
</body>

</html>