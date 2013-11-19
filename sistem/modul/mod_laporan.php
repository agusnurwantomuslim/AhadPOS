 <?php
/* mod_laporan.php ------------------------------------------------------
   	version: 1.01

	Part of AhadPOS : http://ahadpos.com
	License: GPL v2
			http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
			http://vlsm.org/etc/gpl-unofficial.id.html

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License v2 (links provided above) for more details.
----------------------------------------------------------------*/


include "../../config/config.php";
// check_user_access(basename($_SERVER['SCRIPT_NAME']));

echo "
	<link href='../../config/adminstyle.css' rel='stylesheet' type='text/css' />

        <SCRIPT TYPE='text/javascript'>
        <!--
        function popupform(myform, windowname)
        {
                if (! window.focus)return true;
                window.open('', windowname, 'type=fullWindow,fullscreen,scrollbars=yes');
                myform.target=windowname;
                return true;
        }
        //-->
        </SCRIPT>
	";




switch($_GET[act]){ //------------------------------------------------------------------------

    default:

        echo "<h2>Laporan Manajemen</h2>

		<table>
		<tr>

		<td>
			<form method=POST action='?module=laporan&act=penjualan1'>
			<input type=submit value='(j) Laporan Penjualan' accesskey='j'>
			</form>
		</td>

		<td>
			<form method=POST action='?module=laporan&act=pembelian1'>
			<input type=submit value='(b) Laporan Pembelian' accesskey='b'>
			</form>
		</td>

		<td>
			<form method=POST action='?module=laporan&act=total1'>
			<input type=submit value='(t) Total Stok' accesskey='t'>
			</form>
		</td>

		</tr>

		<tr>
	
			<td>
			<form method=POST action='?module=laporan&act=toprank1'>
			<input type=submit value='(r) Top Rank' accesskey='r'>
			</form>
			</td>

	
			<td>
			<form method=POST action='?module=laporan&act=aging1'>
			<input type=submit value='(a) Aging' accesskey='a'>
			</form>
			</td>

		</tr>

		<tr>
	
			<td>
			<form method=POST action='?module=laporan&act=po'>
			<input type=submit value='(p) Purchase Order' accesskey='r'>
			</form>
			</td>
		</tr>

	
		</table>

	";

        break;



    case "penjualan1":  // ===========================================================================================================


	echo "<h2>Laporan Penjualan</h2>";

	// ambil daftar nama kasir
	// idLevelUser : 4 = kasir
	$sql="SELECT namaUser, idUser  
		FROM user   
		WHERE idLevelUser = 4 ORDER BY namaUser ASC";
	$namaKasir=mysql_query($sql);


	echo "
	<form method=GET action='?module=laporan'>

		<input type=hidden name=module value=laporan>
		<input type=hidden name=act value=penjualan2>
	
	<table>
        <tr>
		<td>(d) Dari Tanggal </td>
		<td>: <input type=text name='DariTanggal' value='".date("Y-m-d 00:00:00")."' accesskey='d'></td>
	</tr>
        <tr>
		<td>Sampai Tanggal </td>
		<td>: <input type=text name='SampaiTanggal' value='".date("Y-m-d 23:59:59")."'></td>
	</tr>
        <tr>
		<td>Pilih Kasir </td>
		<td>: <select name='idKasir'> <option value='SEMUA' selected>SEMUA</option>";
	while($kasir = mysql_fetch_array($namaKasir)){
		echo "<option value='$kasir[idUser]'>$kasir[namaUser]</option>\n";
	}	

	echo "
		</td>
	</tr>

        <tr><td colspan=2>&nbsp;</td></tr>
        <tr><td colspan=2><input type=submit value='Buat Laporan'>&nbsp;&nbsp;&nbsp;
                                <input type=reset value='Batal'></td></tr>
    </table>
 </form>


	";

	break;



    case "penjualan2":  // ===========================================================================================================


	// ambil daftar nama kasir
	if ($_GET[idKasir]=='SEMUA') {
		$x[namaUser] = 'SEMUA';
	} else {
		$sql="SELECT namaUser FROM user WHERE idUser = $_GET[idKasir]";
		$hasil=mysql_query($sql); $x = mysql_fetch_array($hasil);
	}

	echo "
              <br/>
              <h2>Laporan Penjualan</h2>

		<h3>Kasir: $x[namaUser], Dari: $_GET[DariTanggal], Sampai: $_GET[SampaiTanggal]</h3>

              <table class=tableku>
              <tr><th>No.Struk</th><th>Waktu</th><th>Total Transaksi</th><th>Aksi</th><th>Hapus?</th></tr>";

		if ($_GET[idKasir]=='SEMUA') {
			$sql = "SELECT t.idTransaksiJual, t.tglTransaksiJual, t.nominal   
				FROM transaksijual AS t
				WHERE t.tglTransaksiJual BETWEEN '$_GET[DariTanggal]' AND '$_GET[SampaiTanggal]'  
 					ORDER BY t.idTransaksiJual ASC";
		} else {
			$sql = "SELECT t.idTransaksiJual, t.tglTransaksiJual, t.nominal 
				FROM transaksijual AS t
				WHERE t.idUser = $_GET[idKasir] 
					AND t.tglTransaksiJual BETWEEN '$_GET[DariTanggal]' AND '$_GET[SampaiTanggal]' ORDER BY t.idTransaksiJual ASC";
		}
                $tampil=mysql_query($sql);

                $no=1;
		$total_transaksi = 0;
                while ($r=mysql_fetch_array($tampil)){
                    //untuk mewarnai tabel menjadi selang-seling
                    if(($no % 2) == 0){
                        $warna = "#EAF0F7";
                    }
                    else{
                        $warna = "#FFFFFF";
                    }
                    echo "<tr bgcolor=$warna>";
                   echo "
			<td class=td><center> 		$r[idTransaksiJual] </center></td>
			<td class=td><center> 		".date("H:i:s", strtotime($r[tglTransaksiJual]))." </center></td>

			<td class=td align=right> 	".number_format($r[nominal],0,',','.')."</td>
			<td class=td>	<a href='?module=laporan&act=aksi&action=cetakjual1&id=$r[idTransaksiJual]&kasir=$x[namaUser]'>Cetak</a> | 
					<a href='?module=laporan&act=aksi&action=lihatjual&id=$r[idTransaksiJual]&kasir=$x[namaUser]'>Lihat</a></td>
			<td class=td align=right> Ha<a href='?module=laporan&act=aksi&action=hapusjual&id=$r[idTransaksiJual]&idKasir=$_GET[idKasir]&DariTanggal=$_GET[DariTanggal]&SampaiTanggal=$_GET[SampaiTanggal]'>p</a>us</td>
			</tr>";

			//fixme: tampilkan juga profit dari setiap invoice

			$total_transaksi = $total_transaksi + $r[nominal];
			$no++;
                }


		// hitung profit
		if ($_GET[idKasir]=='SEMUA') {
			$sql = "SELECT SUM((d.hargaJual - d.hargaBeli) * jumBarang) AS profit  
				FROM transaksijual AS t, detail_jual AS d 
				WHERE t.tglTransaksiJual BETWEEN '$_GET[DariTanggal]' AND '$_GET[SampaiTanggal]' AND t.idTransaksiJual=d.nomorStruk 
 					ORDER BY t.idTransaksiJual ASC";
		} else {
			$sql = "SELECT SUM((d.hargaJual - d.hargaBeli) * jumBarang) AS profit 
				FROM transaksijual AS t, detail_jual AS d 
				WHERE t.idUser = $_GET[idKasir] AND t.idTransaksiJual=d.nomorStruk 
				AND t.tglTransaksiJual BETWEEN '$_GET[DariTanggal]' AND '$_POST[SampaiTanggal]' ORDER BY t.idTransaksiJual ASC";
		};
                $tampil=mysql_query($sql);
                $r=mysql_fetch_array($tampil);	
		$total_profit = 0;
		$total_profit = $total_profit + $r[profit];

                echo "</table>

			<h2>Total Transaksi: ".number_format($total_transaksi,0,',','.')."</h2>
			<h2>Total Profit: ".number_format($total_profit,0,',','.')."</h2>

                	<p>&nbsp;</p>	
	                <a href=javascript:history.go(-1)><< Kembali</a>

		";

        break;



    case "aksi":  // ===========================================================================================================

	
	if ($_GET[action] == 'hapusjual') { // ---------------------------------------------------------------------------------

		// cek transaksi jualnya
		$sql	= "SELECT idBarang, barcode, jumBarang, hargaJual FROM detail_jual WHERE nomorStruk = $_GET[id]";
		$hasil	= mysql_query($sql);

		$grandtotal = 0;
		while ($x = mysql_fetch_array($hasil)) {

		$grandtotal = $grandtotal + ($x[jumBarang] * $x[hargaJual]);

		// kembalikan jumlah stok sebelumnya di tabel barang
			// cari jumlah saat ini di table barang
			$sql	= "SELECT jumBarang FROM barang WHERE barcode='".$x[barcode]."'";
			$hasil1	= mysql_query($sql);
			$x1	= mysql_fetch_array($hasil1);

			$jumlahbaru	= $x1[jumBarang] + $x[jumBarang];
			// simpan jumlah yang baru
			$sql	= "UPDATE barang SET jumBarang=$jumlahbaru WHERE barcode='".$x[barcode]."'";
			$hasil1 = mysql_query($sql);

		// kembalikan jumlah stok sebelumnya di tabel detail_beli
			// cari jumlah saat ini di table barang
			$BarangHabis	= false;
			$sql	= "SELECT jumBarang FROM detail_beli WHERE barcode='".$x[barcode]."' 
					AND isSold='N' AND idBarang=".$x[idBarang]."
					ORDER BY idDetailBeli ASC";
			$hasil2	= mysql_query($sql);

			// kalau tidak ada yang ketemu, cari lagi - kali ini isSold = 'Y'
			if (mysql_num_rows($hasil2) < 1) { 
				$sql	= "SELECT jumBarang FROM detail_beli WHERE barcode='".$x[barcode]."' 
						AND isSold='Y' AND idBarang=".$x[idBarang]." 
						ORDER BY idDetailBeli DESC";
				$hasil2	= mysql_query($sql);
				$BarangHabis	= true;
			};
			$x2	= mysql_fetch_array($hasil2);

			$jumlahbaru	= $x2[jumBarang] + $x[jumBarang];
			// simpan jumlah yang baru
			if ($BarangHabis) {
				$sql	= "UPDATE detail_beli SET jumBarang=$jumlahbaru, isSold='N' WHERE idBarang='".$x[idBarang]."'";
			} else {
				$sql	= "UPDATE detail_beli SET jumBarang=$jumlahbaru WHERE idBarang='".$x[idBarang]."'";
			};
			$hasil1 = mysql_query($sql);

		}; // while ($x = mysql_fetch_array($hasil))

		// simpan audit trail nya
		$sql = "INSERT INTO audit (jenisTransaksi, username, tglTransaksi, 
				nomorStruk, nominalStruk) 
			VALUES ('returnotajual', '$_SESSION[uname]', '".date("Y-m-d H:i:s")."',
				 $_GET[id], $grandtotal)";
		$hasil 	= mysql_query($sql) or die(mysql_error());


		// hapus di transaksi_jual
		$sql = "DELETE FROM transaksijual WHERE idTransaksiJual = $_GET[id]";
		$hasil 	= mysql_query($sql) or die(mysql_error());

		// hapus juga seluruh transaksinya di detail_jual
		$sql = "DELETE FROM detail_jual WHERE nomorStruk = $_GET[id]";
		$hasil 	= mysql_query($sql) or die(mysql_error());

		// module=laporan&act=penjualan2&DariTanggal=2010-08-02+00%3A00%3A00&SampaiTanggal=2010-08-02+23%3A59%3A59&idKasir=SEMUA
		header("location:media.php?module=laporan&act=penjualan2&DariTanggal=$_GET[DariTanggal]&SampaiTanggal=$_GET[SampaiTanggal]&idKasir=$_GET[idKasir]");


	}

	if ($_GET[action] == 'cetakjual1') { // ---------------------------------------------------------------------------------

		// pilih printer
		$sql 	= "SELECT namaWorkstation,printer_commands,workstation_address FROM workstation ";
		$hasil 	= mysql_query($sql) or die(mysql_error());

		echo "
			<form method=POST action='?module=laporan&act=aksi&action=cetakjual2'>
	
		<table>
        	<tr>
			<td>Pilih Printer </td>
			<td>: <select name='namaPrinter'>";
		
		while($printer = mysql_fetch_array($hasil)){
			echo "<option value='$printer[printer_commands]'>$printer[namaWorkstation]</option>\n";
		}	

		echo "
			</td>
			</tr>

        		<tr><td colspan=2>&nbsp;</td></tr>
        		<tr><td colspan=2><input type=submit value='Pilih Printer'>&nbsp;&nbsp;&nbsp;
        	                        <input type=reset value='Batal'></td></tr>
			</table>
			
			<input type=hidden name=idTransaksi value='$_GET[id]'>
			<input type=hidden name=namaKasir value='$_GET[kasir]'>

			</form>
		";

		// tampilkan link untuk kembali
		echo "<br /><br />    <a href=javascript:history.go(-1)><< Kembali</a>";

	} // if ($_GET[action] == 'cetakjual1')



	if ($_GET[action] == 'cetakjual2') { // ---------------------------------------------------------------------------------

		// ambil info struk ybs
		$sql = "SELECT nominal,uangDibayar FROM transaksijual WHERE idTransaksiJual=$_POST[idTransaksi]";
		//echo $sql;
		$hasil 	= mysql_query($sql); $x = mysql_fetch_array($hasil);
		$totalTransaksi = $x[nominal];
		$uangDibayar	= $x[uangDibayar];

		// ambil transaksi yang akan dicetak
		$sql = "SELECT t.jumBarang,t.hargaJual,b.namaBarang FROM barang AS b, detail_jual AS t
			WHERE t.nomorStruk='$_POST[idTransaksi]' AND t.barcode=b.barcode";
		//echo $sql;
		$hasil 	= mysql_query($sql);
		
		// cetak struk
		cetakStruk ("$_POST[namaPrinter]", $_POST[idTransaksi], "$_POST[namaKasir]", $totalTransaksi, $uangDibayar, $hasil);
		
		// tampilkan link untuk kembali
		echo "<br /><br />    <a href=javascript:history.go(-1)><< Kembali</a>";
	}


	if ($_GET[action] == 'lihatjual') { // ---------------------------------------------------------------------------------

		if ($_GET[kasir]=='SEMUA') {
			$namaKasir='SEMUA';
		} else {
			$namaKasir=$_GET[kasir];
		}

	echo "
              <br/>
              <h2>Detail Penjualan</h2>

		<h3>Kasir: $namaKasir, No.Struk: $_GET[id]</h3>

              <table class=tableku>
              <tr><th>Barcode</th><th>Nama Barang</th><th>Harga Jual</th><th>Harga Beli</th><th>Jumlah</th><th>Total</th></tr>";

		$sql = "SELECT d.barcode, b.namaBarang, d.hargaJual, d.hargaBeli, d.jumBarang 
				FROM detail_jual AS d, barang AS b 
				WHERE d.nomorStruk = $_GET[id] AND d.barcode = b.barcode";
                $tampil=mysql_query($sql);

                $no=1;
		$total_transaksi= 0;
		$total_profit 	= 0;
                while ($r=mysql_fetch_array($tampil)){
                    //untuk mewarnai tabel menjadi selang-seling
                    if(($no % 2) == 0){
                        $warna = "#EAF0F7";
                    }
                    else{
                        $warna = "#FFFFFF";
                    }
                    echo "<tr bgcolor=$warna>";
                   echo "
			<td class=td> 		$r[barcode]	</td>
			<td class=td> 		$r[namaBarang]	</td>
			<td class=td align=right>	".number_format($r[hargaJual],0,',','.')." </td>
			<td class=td align=right>	".number_format($r[hargaBeli],0,',','.')." </td>
			<td class=td align=right>	".number_format($r[jumBarang],0,',','.')." </td>
			<td class=td align=right>	".number_format(($r[hargaJual] * $r[jumBarang]),0,',','.')." </center></td>

			</tr>";

			$total_transaksi = $total_transaksi + ($r[hargaJual] * $r[jumBarang]);
			$total_profit = $total_profit + (($r[hargaJual] - $r[hargaBeli]) * $r[jumBarang]);
			$no++;
                }

                echo "</table>

			<h3>Total Transaksi: ".number_format($total_transaksi,0,',','.')."</h3>
			<h3>Total Profit: ".number_format($total_profit,0,',','.')."</h3>

                	<p>&nbsp;</p>
	                <a href=javascript:history.go(-1)><< Kembali</a>
		";


	}

	break;


  case 'total1': { // ---------------------------------------------------------------------------------

	echo "
              <br/>
              <h2>Laporan Total Stok</h2>

		";

//	Ini salah, karena kalau ada 1 saja record barang yang sudah sold-out / isSold='Y' 
// 	-- maka seluruh barang tersebut jadi tidak dihitung lagi stoknya
//	Biarkan penentu keberadaan stok adalah field jumBarang di table barang
//		$sql	= "SELECT SUM(b.jumBarang * d.hargaBeli) AS TotalStok 
//			FROM barang AS b, (SELECT barcode, hargaBeli FROM detail_beli WHERE isSold='N' AND hargaBeli > 0 GROUP BY barcode) AS d 
//			WHERE b.jumBarang > 0 AND b.barcode = d.barcode";		

/*		// query ini mengambil hargaBeli yang paling pertama / lama
		$sql	= "SELECT SUM(b.jumBarang * d.hargaBeli) AS TotalStok 
			FROM barang AS b, (SELECT barcode, hargaBeli FROM detail_beli WHERE hargaBeli > 0 GROUP BY barcode) AS d 
			WHERE b.jumBarang > 0 AND b.barcode = d.barcode";		
*/

		// query ini mengambil hargaBeli yang terbaru
		$sql	= "SELECT SUM(b.jumBarang * d.hargaBeli) AS TotalStok 
			FROM barang AS b, 
			(SELECT barcode, hargaBeli FROM 
				(SELECT barcode, hargaBeli, idTransaksiBeli 
				 FROM detail_beli WHERE hargaBeli > 0 ORDER BY idTransaksiBeli DESC) AS d1 
			GROUP BY barcode) AS d 
			WHERE b.jumBarang > 0 AND b.barcode = d.barcode";		

		$tampil	= mysql_query($sql);
		$x 	= mysql_fetch_array($tampil);

                echo "Total Stok Saat Ini = Rp ".number_format($x[TotalStok],0,',','.')." 

                	<p>&nbsp;</p>	
	                <a href=javascript:history.go(-1)><< Kembali</a>

		";

        exit;

	}


  case 'toprank1': { // ---------------------------------------------------------------------------------

	$tanggal = date('Y-m-d');
	echo "
              <br/>
              <h2>Laporan Top Rank</h2>

			<form method=POST action='modul/mod_laporan.php?act=toprank2' onSubmit=\"popupform(this, 'top-rank')\">
	
		<table>
        	<tr>
			<td>Dari Tanggal </td>
			<td>: <input type=text name=dari value='$tanggal'>
			</td>
		</tr>

        	<tr>
			<td>Sampai Tanggal </td>
			<td>: <input type=text name=sampai value='$tanggal'>
			</td>
		</tr>

        	<tr>
			<td>Kategori </td>
			<td>: 	<select name='kategori'> 
				<option value='0' selected>--pilih--</option>
				<option value='SEMUA'>SEMUA</option>";
		$hasil	= mysql_query("SELECT idKategoriBarang, namaKategoriBarang FROM kategori_barang");
		while ($x = mysql_fetch_array($hasil)) {
			echo "<option value='".$x['idKategoriBarang']."'>".$x['namaKategoriBarang']."</option>";
		};

		echo "		</select>
			</td>
		</tr>

        	<tr>
			<td>Rack </td>
			<td>: 	<select name='rak'> 
				<option value='0' selected>--pilih--</option>
				<option value='SEMUA'>SEMUA</option>";
		$hasil	= mysql_query("SELECT idRak, namaRak FROM rak");
		while ($x = mysql_fetch_array($hasil)) {
			echo "<option value='".$x['idRak']."'>".$x['namaRak']."</option>";
		};

		echo "		</select>
			</td>
		</tr>

        	<tr>
			<td>Jumlah Item </td>
			<td>: <input type=text name=jumlah value='200'>
			</td>
		</tr>

        	<tr>
			<td>Sortir berdasarkan</td>
			<td>: 	<select name='sortir'> 
				<option value='jumlah' selected>jumlah</option>
				<option value='omset' >		omset</option>
				<option value='profit'>		profit</option>
				</select>
			</td>
		</tr>


        		<tr><td colspan=2><input type=submit value='Buat Laporan'></td></tr>
		</table>
			
		</form>

		";
	exit;
	}

  case 'toprank2': { // ---------------------------------------------------------------------------------

	if ($_POST['kategori'] == 'SEMUA') {
		$kategori = 'SEMUA'; 
	} else {
		$hasil 	= mysql_query("SELECT namaKategoriBarang FROM kategori_barang WHERE idKategoriBarang=".$_POST['kategori']);
		$x	= mysql_fetch_array($hasil);
		$kategori = $x['namaKategoriBarang'];
	};

	if ($_POST['rak'] == 'SEMUA') {
		$rak = 'SEMUA'; 
	} else {
		$sql ="SELECT namaRak FROM rak WHERE idRak=".$_POST['rak'];
		$hasil 	= mysql_query($sql);
		$x	= mysql_fetch_array($hasil);
		$rak = $x['namaRak'];
	};

	if ($_POST['kategori'] == 'SEMUA') { 
		$idKategoriBarang = '';	
	} else {
		$idKategoriBarang = 'AND b.idKategoriBarang = '.$_POST['kategori'];	
	};

	if ($_POST['rak'] == 'SEMUA') { 
		$idRak = '';	
	} else {
		$idRak = 'AND b.idRak = '.$_POST['rak'];	
	};

	$sortir = $_POST['sortir'];
	$sql	= "SELECT lb.barcode, lb.namaBarang, COUNT(lb.barcode) AS jumlah, SUM(lb.hargaJual) AS omset, 
			SUM(lb.hargaJual - lb.hargaBeli) AS profit, lb.jumBarang 
		FROM 	(SELECT dj.barcode AS barcode, b.namaBarang AS namaBarang, b.jumBarang, dj.hargaJual, dj.hargaBeli, b.idKategoriBarang  
      			FROM barang AS b, 
             			(SELECT barcode, hargaJual, hargaBeli FROM detail_jual AS j, 
                    			(SELECT idTransaksiJual AS nomorStruk FROM transaksijual 
                    			WHERE tglTransaksiJual BETWEEN '".$_POST['dari']." 00:00:01' AND '".$_POST['sampai']." 23:59:59') AS t
             			WHERE j.nomorStruk = t.nomorStruk) AS dj  
			WHERE dj.barcode = b.barcode  $idKategoriBarang ORDER BY dj.barcode) AS lb 
		GROUP BY lb.barcode 
		ORDER BY $sortir DESC
		LIMIT ".$_POST['jumlah'].";
		";

	if ($_POST['rak'] <> 0) {
	$sql	= "SELECT lb.barcode, lb.namaBarang, COUNT(lb.barcode) AS jumlah, SUM(lb.hargaJual) AS omset, 
			SUM(lb.hargaJual - lb.hargaBeli) AS profit, lb.jumBarang 
		FROM 	(SELECT dj.barcode AS barcode, b.namaBarang AS namaBarang, b.jumBarang, dj.hargaJual, dj.hargaBeli, b.idKategoriBarang  
      			FROM barang AS b, 
             			(SELECT barcode, hargaJual, hargaBeli FROM detail_jual AS j, 
                    			(SELECT idTransaksiJual AS nomorStruk FROM transaksijual 
                    			WHERE tglTransaksiJual BETWEEN '".$_POST['dari']." 00:00:01' AND '".$_POST['sampai']." 23:59:59') AS t
             			WHERE j.nomorStruk = t.nomorStruk) AS dj  
			WHERE dj.barcode = b.barcode  $idRak ORDER BY dj.barcode) AS lb 
		GROUP BY lb.barcode 
		ORDER BY $sortir DESC
		LIMIT ".$_POST['jumlah'].";
		";
	};
	$hasil	= mysql_query($sql) or die("Error : ".mysql_error());
	//echo $sql;

	echo "
		<br/>
		<h2>Laporan Top Rank</h2>
		Tanggal:".$_POST['dari']." s/d ".$_POST['sampai'];

		if ($_POST['rak'] <> 0) {
			echo " Rak: $rak";
		} else {
			echo " Kategori: $kategori";
		};
	echo "
		<table>
		<tr>
			<td class=td><b><center>No.</center></b></td>
			<td class=td><b><center>Barcode</center></b></td>
			<td class=td><b><center>Nama Barang</center></b></td>
			<td class=td><b><center>Jumlah</center></b></td>
			<td class=td><b><center>Omset</center></b></td>
			<td class=td><b><center>Profit</center></b></td>
			<td class=td><b><center>Avg / day</center></b></td>
			<td class=td><b><center>Total Stok</center></b></td>
		</tr>
		";

	$start = strtotime($_POST['dari']);
	$end = strtotime($_POST['sampai']);
	$jmlhari = abs($end - $start) / 86400;

	$no=0;
	while ($x=mysql_fetch_array($hasil)){
		//untuk mewarnai tabel menjadi selang-seling
		$no++;
		if(($no % 2) == 0){
			$warna = "#EAF0F7";
		} else {
			$warna = "#FFFFFF";
		}


		echo "<tr bgcolor=$warna>";
		echo "
			<td class=td align=center> $no </td>
			<td class=td> ".$x['barcode']." </td>
			<td class=td> ".$x['namaBarang']." </td>
			<td class=td align=right> ".number_format($x['jumlah'],0,',','.')." </td>
			<td class=td align=right> ".number_format($x['omset'],0,',','.')." </td>
			<td class=td align=right> ".number_format($x['profit'],0,',','.')." </td>
			<td class=td align=right> ".number_format(($x['jumlah'] / $jmlhari),2,',','.')." </td>
			<td class=td align=right> ".number_format($x['jumBarang'],0,',','.')." </td>
			</tr>";
	};
	echo "</table>";

	exit;
	}


  case 'aging1': { // ---------------------------------------------------------------------------------

	$tanggal = date('Y-m-d');
	echo "
              <br/>
              <h2>Laporan Aging / Barang Mati</h2>

			<form method=POST action='modul/mod_laporan.php?act=aging2' onSubmit=\"popupform(this, 'aging')\">
	
		<table>
        	<tr>
			<td>Dari Tanggal </td>
			<td>: <input type=text name=dari value='$tanggal'>
			</td>
		</tr>

        	<tr>
			<td>Sampai Tanggal </td>
			<td>: <input type=text name=sampai value='$tanggal'>
			</td>
		</tr>

        	<tr>
			<td>Kategori </td>
			<td>: 	<select name='kategori'> 
				<option value='SEMUA' selected>SEMUA</option>";
		$hasil	= mysql_query("SELECT idKategoriBarang, namaKategoriBarang FROM kategori_barang");
		while ($x = mysql_fetch_array($hasil)) {
			echo "<option value='".$x['idKategoriBarang']."'>".$x['namaKategoriBarang']."</option>";
		};

		echo "		</select>
			</td>
		</tr>

        	<tr>
			<td>Jumlah Item </td>
			<td>: <input type=text name=jumlah value='200'>
			</td>
		</tr>

        	<tr>
			<td>Sortir berdasarkan</td>
			<td>: 	<select name='sortir'> 
				<option value='avgSales' selected>	Average Daily Sales</option>
				<option value='jmlStokIni' >		Jumlah Sisa Stok</option>
				<option value='umurStok' >		Umur Stok</option>
				<option value='nilaiStok'>		Nilai Stok</option>
				</select>
			</td>
		</tr>


        		<tr><td colspan=2><input type=submit value='Buat Laporan'></td></tr>
		</table>
			
		</form>

		";
	exit;
	}

  case 'aging2': { // ---------------------------------------------------------------------------------

	if ($_POST['kategori'] == 'SEMUA') {
		$kategori = 'SEMUA'; 
	} else {
		$hasil 	= mysql_query("SELECT namaKategoriBarang FROM kategori_barang WHERE idKategoriBarang=".$_POST['kategori']);
		$x	= mysql_fetch_array($hasil);
		$kategori = $x['namaKategoriBarang'];
	};

	if ($_POST['kategori'] == 'SEMUA') { 
		$idKategoriBarang = '';	
	} else {
		$idKategoriBarang = 'AND b.idKategoriBarang = '.$_POST['kategori'];	
	};

	// buat temporary table untuk simpan hasil
	$sql = "
		CREATE TABLE IF NOT EXISTS `tmp_lap_aging` (
		  `uid` bigint(20) NOT NULL AUTO_INCREMENT,
		  `barcode` varchar(25) DEFAULT NULL,
		  `namaBarang` varchar(30) DEFAULT ' ',
		  `nilaiStok` bigint(20) DEFAULT '0',
		  `umurStok` int(10) DEFAULT '0',
		  `jmlStokIni` int(10) DEFAULT '0',
		  `jmlStokSemua` int(10) DEFAULT '0',
		  `avgSales` DECIMAL (6,6) DEFAULT '0',

		  PRIMARY KEY `uid` (`uid`),
		  KEY `avgSales` (`avgSales`)
		) ENGINE=MEMORY DEFAULT CHARSET=latin1;
		";
	$hasil	= mysql_query($sql) or die("Error : ".mysql_error());

	$sortir = $_POST['sortir'];

	$sql 	= "SELECT lb.barcode, lb.namaBarang, SUM(lb.jumBarang) AS sisastok, 
			SUM(lb.hargaBeli * lb.jumBarang) AS nilaistok, 
			(TIMESTAMPDIFF(DAY, lb.tglTransaksiBeli, NOW())) AS umurstok, 
			lb.tglTransaksiBeli, lb.TotalJumlah    

		FROM (
			SELECT dj.barcode AS barcode, b.namaBarang AS namaBarang, dj.jumBarang, 
				dj.jumBarangAsli, dj.hargaBeli, b.idKategoriBarang, dj.tglTransaksiBeli, 
				b.jumBarang AS TotalJumlah   
			FROM barang AS b, (
				SELECT b.barcode, b.hargaBeli, b.jumBarang, b.jumBarangAsli, t.tglTransaksiBeli 
				FROM detail_beli AS b, (
					SELECT idTransaksiBeli, tglTransaksiBeli 
					FROM transaksibeli 
					WHERE tglTransaksiBeli BETWEEN '".$_POST['dari']."' AND '".$_POST['sampai']."'
					) AS t 
				WHERE isSold = 'N' AND t.idTransaksiBeli = b.idTransaksiBeli AND b.jumBarang > 0
				) AS dj 
			WHERE dj.barcode = b.barcode   $idKategoriBarang   ORDER BY dj.barcode
			) AS lb 

		GROUP BY lb.barcode 
		LIMIT ".$_POST['jumlah']."; 
		";

	$hasil	= mysql_query($sql) or die("Error : ".mysql_error());

	// masukkan ke temporary table
	$sqltmp = "INSERT INTO tmp_lap_aging (barcode,namaBarang,nilaiStok,umurStok,jmlStokIni,jmlStokSemua,avgSales) VALUES ";
	while ($x = mysql_fetch_array($hasil)) {
		// hitung Average Sales / Day
		$sql	= "
			SELECT SUM(jumBarang) AS total 
			FROM detail_jual AS dj, 
					(
					SELECT idTransaksiJual 
					FROM transaksijual 
					WHERE tglTransaksiJual BETWEEN '".$_POST['dari']."' AND '".$_POST['sampai']."') AS tj 
			WHERE barcode='".$x['barcode']."' AND dj.nomorStruk = tj.idTransaksiJual";
		$hasil3	= mysql_query($sql);
		$y	= mysql_fetch_array($hasil3);
		$avgSales	= ($y['total'] / $jmlhari); 

		// buat statement SQL 
		$sqltmp .= "('".$x['barcode']."','".$x['namaBarang']."','".$x['nilaistok']."','".$x['umurstok']."',
			'".$x['sisastok']."','".$x['TotalJumlah']."','$avgSales'),";
	};	
	// hapus koma di akhir string
	$sqltmp = substr($sqltmp, 0, -1);
	// simpan ke temporary table
	$hasil	= mysql_query($sqltmp) or die("Error : ".mysql_error());

	echo "
		<br/>
		<h2>Laporan Aging</h2>
		Tanggal:".$_POST['dari']." s/d ".$_POST['sampai']." Kategori: $kategori

		<table>
		<tr>
			<td class=td><b><center>No.</center></b></td>
			<td class=td><b><center>Barcode</center></b></td>
			<td class=td><b><center>Nama Barang</center></b></td>
			<td class=td><b><center>Nilai Stok</center></b></td>
			<td class=td><b><center>Umur Stok</center></b></td>
			<td class=td><b><center>Sisa Stok<br />(periode ini)</center></b></td>
			<td class=td><b><center>Sisa Stok<br />(semua / saat ini)</center></b></td>
			<td class=td><b><center>Avg<br />Daily<br />Sales</center></b></td>
		</tr>
		";

	// ambil data dari temporary table
	if ($sortir=='avgSales') { $sortir = 'avgSales,nilaiStok';};
	$sql = "SELECT * FROM tmp_lap_aging ORDER BY $sortir DESC";
	$hasil	= mysql_query($sql) or die("Error : ".mysql_error());


	$start 	= strtotime($_POST['dari']);
	$end 	= strtotime(time());
	$jmlhari= abs($end - $start) / 86400;

	$no	= 0;
	$nilai	= 0;
	while ($x=mysql_fetch_array($hasil)){

		
		//untuk mewarnai tabel menjadi selang-seling
		$no++;
		if(($no % 2) == 0){
			$warna = "#EAF0F7";
		} else {
			$warna = "#FFFFFF";
		}

		echo "<tr bgcolor=$warna>";
		echo "
			<td class=td align=center> $no </td>
			<td class=td> ".$x['barcode']." </td>
			<td class=td> ".$x['namaBarang']." </td>
			<td class=td align=right> ".number_format($x['nilaiStok'],0,',','.')." </td>
			<td class=td align=right> ".number_format($x['umurStok'],0,',','.')." </td>
			<td class=td align=right> <center>".number_format($x['jmlStokIni'],0,',','.')." </td>
			<td class=td align=right> <center>".number_format($x['jmlStokSemua'],0,',','.')." </td>
			<td class=td align=right> ".number_format($x['avgSales'],6,',','.')." </td>
			</tr>";
		$nilai = $nilai + ($x['nilaiStok'] / $x['jmlStokIni'] * $x['jmlStokSemua']);
	};
	echo "</table> Nilai Stok : Rp ".number_format($nilai,0,',','.');

	// bersihkan temporary table
	$hasil = mysql_query("DELETE FROM tmp_lap_aging");

	exit;
	}

    case "po"; // =======================================================================================================================
        echo "<h2>Purchase Order</h2>
            <form method=POST action='?module=laporan&act=po&action=pesanbarang'>
                Supplier : 
                <select name=supplierId>";
            $supplier = getSupplier();
            while($dataSupplier = mysql_fetch_array($supplier)){
                echo "<option value=$dataSupplier[idSupplier]>$dataSupplier[namaSupplier]::$dataSupplier[alamatSupplier]</option>";
            }
        echo "</select>
		<br />
		Tampilkan hanya barang dengan jumlah lebih kecil dari : <input type=text name=jumlahmin value=0 size=3>
		<br />
            &nbsp;&nbsp;&nbsp;&nbsp;
            <input type=submit value=Pilih>
            </form>";

        if($_GET[action] == 'pesanbarang'){
            
            $supplier = getDetailSupplier($_POST[supplierId]);
            $detailSupplier = mysql_fetch_array($supplier);
            echo "<h2>Pesan Barang di Supplier $detailSupplier[namaSupplier]</h2>
            <br/>Alamat Supplier : $detailSupplier[alamatSupplier]<br/><br/>
            <form method=POST action='modul/js_cetak_PO.php'   onSubmit=\"popupform(this, 'Purchase_Order')\">
            <table width=500>
                <tr><th>#</th><th>No</th><th>Barcode</th><th>Nama Barang</th><th>Stok<br />Saat Ini</th><th>Harga<br />Beli</th></tr>";
            $no = 0;
            $queryBarang = getDaftarBarangSupplier($_POST[supplierId], $_POST[jumlahmin]);
            while($barangSupplier = mysql_fetch_array($queryBarang)){
                if(($no % 2) == 0){
                        $warna = "#EAF0F7";
                    }
                    else{
                        $warna = "#FFFFFF";
                    }
                    echo "<tr bgcolor=$warna>";//end warna
                    echo "<td class=td align=center><input type=checkbox name=cek[] value=$barangSupplier[barcode] id=id$no checked=true></td>";
                    $no++;
                    echo "<td class=td>$no</td>
                        <td class=td>$barangSupplier[barcode]</td>
                        <td class=td>$barangSupplier[namaBarang]</td>
                        <td class=td align=right><center>$barangSupplier[jumBarang]</center></td>
                        <td class=td align=right>$barangSupplier[hargaBeli]</td>
                        </tr>";
            }

                    echo "<input type=hidden name=idSupplier value=$_POST[supplierId]>";
            echo "<tr><td colspan=5 align=center class=td>
            <input type=radio name=pilih onClick='for (i=0;i<$no;i++){document.getElementById(\"id\"+i).checked=true;}'>Check All
            <input type=radio name=pilih onClick='for (i=0;i<$no;i++){document.getElementById(\"id\"+i).checked=false;}'>Uncheck All
            </td></tr>
            <tr>
		<td colspan=3 class=td> 		<input type=checkbox name=cetakcsv> Cetak Excel / CSV</td>
		<td colspan=2 align=right class=td>	<input type=submit value=Cetak></form></td></tr>";
            echo "</table>";
            
        }
        exit;




}


/* CHANGELOG -----------------------------------------------------------

 1.5.5 / 2013-01-22 : Harry Sufehmi	: Penambahan Laporan : Top Rank
 1.5.0 / 2013-01-04 : Harry Sufehmi	: bugfix : perbaikan rumus perhitungan Total Stok
 1.2.5 / 2012-05-14 : Harry Sufehmi	: fitur : audit trail untuk "hapusjual"
 1.2.5 / 2012-04-17 : Harry Sufehmi	: bugfix : perbaikan rumus perhitungan Total Stok
 1.2.5 / 2012-03-04 : Harry Sufehmi	: bugfix : perhitungan Total Stok / total nilai stok kini sudah dari hargaBeli 
						(tadinya dari hargaJual)
 1.2.5 / 2012-02-14 : Harry Sufehmi	: Hapus transaksi jual : kini otomatis mengembalikan jumlah stok barang ke 
						table barang & detail_beli, sejumlah banyak barang yang dibatalkan transaksinya
 1.2.5 / 2012-02-01 : Harry Sufehmi	: Laporan Total Stok
 1.0.1 / 2010-06-03 : Harry Sufehmi	: various enhancements, bugfixes
 0.9.2 / 2010-03-08 : Harry Sufehmi	: initial release

------------------------------------------------------------------------ */

?>
