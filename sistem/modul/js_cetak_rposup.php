<?php

/* js_cetak_rposup.php ----------------------------------------
  version: 1.01

  Part of AhadPOS : http://ahadpos.com
  License: GPL v2
  http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
  http://vlsm.org/etc/gpl-unofficial.id.html

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License v2 (links provided above) for more details.
  ---------------------------------------------------------------- */


include "../../config/config.php";
include "function.php";


session_start();
if (empty($_SESSION['namauser'])) {
	echo "<link href='../config/adminstyle.css' rel='stylesheet' type='text/css'>
 <center>Untuk mengakses modul, Anda harus login <br>";
	echo "<a href=index.php><b>LOGIN</b></a></center>";
} else {

	if (!isset($_SESSION['idCustomer'])) {
		findSupplier($_POST['supplierid']);
		$_SESSION['idCustomer'] = $_SESSION['idSupplier'];
	};

	if (!isset($_SESSION['periode'])) {
		$_SESSION['periode'] = $_POST['periode'];
	};
	if (!isset($_SESSION['range'])) {
		$_SESSION['range'] = $_POST['range'];
	};
	if (!isset($_SESSION['persediaan'])) {
		$_SESSION['persediaan'] = $_POST['persediaan'];
	};

	//var_dump($_SESSION);
};



if ($_GET['init'] == 'yes') {

	// hapus data jadul
	$sql = "DELETE FROM tmp_detail_jual WHERE username = '$_SESSION[uname]'";
	$hasil = mysql_query($sql);
	// buat RPO awal, dan simpan di table tmp_detail_jual
	SimpanRPOawal($_POST['supplierid'], $_POST['range'], $_POST['persediaan'], $_POST['buffer']);
};


// update jumlah pesanan
if ($_POST['buat']) {
	$sql = "DELETE FROM tmp_detail_jual WHERE jumBarang=0";
	$hasil1 = mysql_query($sql);
};


// update jumlah pesanan
if ($_POST['update']) {

	$sql = "SELECT tdj.barcode, tdj.idBarang AS AvgDaily, tdj.hargaBeli, b.namaBarang, 
						tdj.jumBarang, tdj.hargaJual AS StokSaatIni, tdj.tglTransaksi AS SaranOrder 
					FROM tmp_detail_jual AS tdj, barang AS b 
					WHERE tdj.barcode = b.barcode AND tdj.idCustomer = '$_SESSION[idCustomer]' 
						AND tdj.username = '$_SESSION[uname]' ORDER BY b.namaBarang DESC";
	$hasil1 = mysql_query($sql);

	for ($i = 1; $i <= $_POST['count']; $i++) {

		$x = mysql_fetch_array($hasil1);

		if ($x['barcode'] == $_POST["barcode" . $i]) {

			if ($x['jumBarang'] <> $_POST["jumbarang" . $i]) {
				$sql = "UPDATE tmp_detail_jual SET jumBarang = " . $_POST["jumbarang" . $i] . "
								WHERE barcode = '" . $_POST["barcode" . $i] . "' AND username = '$_SESSION[uname]'";
				//echo $sql;
				$hasil2 = mysql_query($sql);
			};
		};
	}; // for ($i = 1; $i <= $_POST['count']; $i++)
};
// edited by abufathir:
if ($_POST['buatpo']) {
	// format isi file CSV :
	// $data[0]  = barcode
	// $data[1]  = idBarang - ignored
	// $data[2]  = namaBarang
	// $data[3]  = jumlah Barang / jumBarang
	// $data[4]  = hargaBeli - ignored
	// $data[5]  = hargaJual (di Gudang) 
	// $data[6]  = RRP (Recommended Retail Price)
	// $data[7]  = namaSatuanBarang
	// $data[8]  = namaKategoriBarang
	// $data[9]  = Supplier - ignored
	// $data[10] = username - ignored
	// persiapan membuat output file CSV
	$csv = "\"barcode\",\"idBarang\",\"namaBarang\",\"jumBarang\",\"hargaBeli\",\"hargaJual\",\"RRP\",\"SatuanBarang\",\"KategoriBarang\",\"Supplier\",\"kasir\"\n";

	// cari nama toko ini 
	$hasil = mysql_query("SELECT value FROM config WHERE `option` = 'store_name'");
	$x = mysql_fetch_array($hasil);
	$namaToko = $x[value];

	$sql = "SELECT tdj.barcode, tdj.idBarang AS AvgDaily, tdj.hargaBeli, b.namaBarang, 
						tdj.jumBarang, tdj.hargaJual AS StokSaatIni, tdj.tglTransaksi AS SaranOrder 
					FROM tmp_detail_jual AS tdj, barang AS b 
					WHERE tdj.barcode = b.barcode AND tdj.idCustomer = '$_SESSION[idCustomer]' 
						AND tdj.username = '$_SESSION[uname]' ORDER BY b.namaBarang DESC";
	$hasil1 = mysql_query($sql);
	$kosong = '';
	
	while ($x = mysql_fetch_array($hasil1)) {
		$csv .= "\"" . $x['barcode'] . "\",\"" . $kosong . "\",\"" . $x['namaBarang'] . "\",\"" .
				$x['jumBarang'] . "\",\"" . $x['hargaBeli'] . "\",\"" .
				$kosong . "\",\"" . $kosong . "\",\"" . $kosong . "\",\"" . $kosong . "\",\"" .
				$kosong . "\",\"" . $kosong . "\",\"\"\n";		
	}

	// masukkan nama toko ini ke nama file csv
	$namaToko = str_replace(' ', '_', $namaToko);
	$namaFile = 'PO-' . $namaToko . "-" . date("Y-m-d--H-i") . ".csv";

	// kirim output CSV ke browser untuk di download
	header("Content-type: text/csv");
	header("Content-Disposition: attachment; filename=\"$namaFile\"");
	header("Pragma: no-cache");
	header("Expires: 0");
	echo $csv;
	// end;
} else {

	echo "<link href='../../config/adminstyle.css' rel='stylesheet' type='text/css'>";
	// edited by abufathir:
	echo $_POST['buat'] ? "<h2>Buat PO (Purchase Order) Per SUPPLIER</h2>" : "<h2>Buat RPO (Rencana Purchase Order) Per SUPPLIER</h2>";
	// end;
	echo "Supplier : " . $_POST['namasupplier'] . "<br />Untuk Persediaan : " . $_POST['persediaan'] . " hari

        
            <form method=POST action='?module=pembelian_barang&act=rposup3'>

			<table class=tableku border=1>
			
			<tr>
				<th>Barcode</th>
				<th>Nama Barang</th>
				<th>Avg<br/>Daily<br/>Sales</th>
				<th>Saran<br/>Order</th>
				<th>Stok<br/>Saat<br/>Ini</th>
				<th>Jumlah<br/>Pesanan</th>
				<th>Harga</th>
				<th>Total</th>
			</tr>
			
			";

	$sql = "SELECT tdj.barcode, tdj.idBarang AS AvgDaily, tdj.hargaBeli, b.namaBarang, 
						tdj.jumBarang, tdj.hargaJual AS StokSaatIni, tdj.tglTransaksi AS SaranOrder 
					FROM tmp_detail_jual AS tdj, barang AS b 
					WHERE tdj.barcode = b.barcode AND tdj.idCustomer = '$_SESSION[idCustomer]' 
						AND tdj.username = '$_SESSION[uname]' ORDER BY b.namaBarang DESC";
	$hasil1 = mysql_query($sql);

	$ctr = 1;
	$grandtotal = 0;
	while ($x = mysql_fetch_array($hasil1)) {

		//untuk mewarnai tabel menjadi selang-seling
		if (($ctr % 2) == 0) {
			$warna = "#EAF0F7";
		} else {
			$warna = "#FFFFFF";
		}

		//$SaranOrder = strtotime($x['SaranOrder']);
		// Saran Order ambil dari field jumBarang; edited by abufathir;
		$SaranOrder = $x['jumBarang'];
		$AvgDaily = round(($x['AvgDaily'] / 100), 2);
		echo "<tr bgcolor=$warna>
				<td>" . $x['barcode'] . "	<input type=hidden name=barcode$ctr value='" . $x['barcode'] . "'></td>
				<td>" . $x['namaBarang'] . "</td>
				<td align=right>" . $AvgDaily . "</td>
				<td align=right>" . $SaranOrder . "</td>
				<td align=right>" . $x['StokSaatIni'] . "</td>
				<td align=center><input type=text name=jumbarang$ctr value=" . $x['jumBarang'] . " size=3></td>
				<td align=right>" . number_format($x['hargaBeli'], 0, ',', '.') . "</td>
				<td align=right>" . number_format(($x['hargaBeli'] * $x['jumBarang']), 0, ',', '.') . "</td>";
		// edited by abufathir:		
		//<td></td>
		// end;		
		echo "	</tr>
				";

		$grandtotal = $grandtotal + ($x['hargaBeli'] * $x['jumBarang']);

		// fixme : 
		//	function di function.php 	untuk cetak struk
		//								untuk save ke CSV	

		$ctr++;
	}; // while ($x = mysql_fetch_array($hasil1))	 	


	echo "<tr>";
	// edited by abufathir:
	echo $_POST['buat'] ? "<td align=left> <input  type=submit 	name=buatpo		value='Buat PO'></td>" : "<td align=left> <input  type=submit 	name=buat		value='Buat RPO'></td>";
	//echo "		<td align=left> <input  type=submit 	name=buat		value='Buat RPO'></td>";
	// end;
	echo "		<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td align=right><input  type=submit 	name=update		value='UPDATE'></td>
				<td></td>
				<td align=right><b>Rp " . number_format($grandtotal, 0, ',', '.') . "</b></td>
			</tr>
					
			</table>
			
			
			
			<input type=hidden name=namasupplier 	value='" . $_POST['namasupplier'] . "'>
			<input type=hidden name=supplierid 		value='" . $_POST['supplierid'] . "'>
			<input type=hidden name=range 			value='" . $_POST['range'] . "'>
			<input type=hidden name=persediaan 		value='" . $_POST['persediaan'] . "'>
			<input type=hidden name=count 			value=$ctr'>
			</form>
			
		";
}; // if ($_POST['submit'])





/* CHANGELOG -----------------------------------------------------------

  1.6.0 / 2013-05-21 : Harry Sufehmi	: initial release
  ------------------------------------------------------------------------ */
?>
