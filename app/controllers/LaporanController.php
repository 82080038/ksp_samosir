<?php
require_once __DIR__ . '/BaseController.php';

class LaporanController extends BaseController {
    public function index() {
        // $this->ensureLoginAndRole([.*]); // DISABLED for development
        $this->render(__DIR__ . '/../views/laporan/index.php');
    }

    public function simpanan() {
        // $this->ensureLoginAndRole([.*]); // DISABLED for development
        // Aggregate simpanan per jenis
        $laporan = fetchAll("SELECT js.nama_simpanan, SUM(s.saldo) as total_saldo, COUNT(s.id) as jumlah_rekening FROM simpanan s LEFT JOIN jenis_simpanan js ON s.jenis_simpanan_id=js.id WHERE s.status='aktif' GROUP BY js.id");
        
        if (isset($_GET['format']) && $_GET['format'] == 'pdf') {
            // Stub: Implement PDF generation with FPDF or TCPDF
            // require 'fpdf/fpdf.php'; $pdf = new FPDF(); $pdf->AddPage(); $pdf->SetFont('Arial','B',16); $pdf->Cell(40,10,'Laporan Simpanan'); foreach ($laporan as $row) { $pdf->Cell(40,10,$row['nama_simpanan'] . ' - ' . $row['total_saldo']); } $pdf->Output();
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="laporan_simpanan.pdf"');
            echo 'PDF Stub: ' . json_encode($laporan); // Placeholder
            exit;
        }
        
        $this->render(__DIR__ . '/../views/laporan/simpanan.php', ['laporan' => $laporan]);
    }

    public function pinjaman() {
        // $this->ensureLoginAndRole([.*]); // DISABLED for development
        $laporan = fetchAll("SELECT jp.nama_pinjaman, SUM(p.jumlah_pinjaman) as total_pinjaman, COUNT(p.id) as jumlah_pinjaman FROM pinjaman p LEFT JOIN jenis_pinjaman jp ON p.jenis_pinjaman_id=jp.id WHERE p.status IN ('disetujui', 'dicairkan') GROUP BY jp.id");
        $this->render(__DIR__ . '/../views/laporan/pinjaman.php', ['laporan' => $laporan]);
    }

    public function penjualan() {
        // $this->ensureLoginAndRole([.*]); // DISABLED for development
        // Aggregate penjualan bulan ini
        $laporan = fetchAll("SELECT MONTH(tanggal_penjualan) as bulan, YEAR(tanggal_penjualan) as tahun, SUM(total_harga) as total_penjualan FROM penjualan WHERE status_pembayaran='lunas' AND MONTH(tanggal_penjualan)=MONTH(CURRENT_DATE) AND YEAR(tanggal_penjualan)=YEAR(CURRENT_DATE) GROUP BY YEAR(tanggal_penjualan), MONTH(tanggal_penjualan) ORDER BY tahun DESC, bulan DESC");
        $this->render(__DIR__ . '/../views/laporan/penjualan.php', ['laporan' => $laporan]);
    }

    public function neraca() {
        // $this->ensureLoginAndRole([.*]); // DISABLED for development
        // Simple neraca: aktiva (kas, simpanan, pinjaman) vs passiva (ekuitas)
        $aktiva = fetchRow("SELECT (SUM(s.saldo) + SUM(p.jumlah_pinjaman)) as total_aktiva FROM simpanan s, pinjaman p WHERE s.status='aktif' AND p.status IN ('disetujui', 'dicairkan')");
        $passiva = fetchRow("SELECT SUM(saldo) as total_passiva FROM coa WHERE tipe='kredit'");
        $this->render(__DIR__ . '/../views/laporan/neraca.php', ['aktiva' => $aktiva['total_aktiva'] ?? 0, 'passiva' => $passiva['total_passiva'] ?? 0]);
    }

    public function labaRugi() {
        // $this->ensureLoginAndRole([.*]); // DISABLED for development
        $pendapatan = fetchRow("SELECT SUM(total_harga) as pendapatan FROM penjualan WHERE status_pembayaran='lunas' AND MONTH(tanggal_penjualan)=MONTH(CURRENT_DATE) AND YEAR(tanggal_penjualan)=YEAR(CURRENT_DATE)")['pendapatan'] ?? 0;
        $beban = fetchRow("SELECT SUM(total) as beban FROM (SELECT SUM(jumlah) as total FROM transaksi_simpanan WHERE jenis_transaksi='penarikan' UNION SELECT SUM(jumlah_pinjaman) FROM pinjaman WHERE status='dicairkan') as b")['beban'] ?? 0;
        $this->render(__DIR__ . '/../views/laporan/laba_rugi.php', ['pendapatan' => $pendapatan, 'beban' => $beban]);
    }
}
