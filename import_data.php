<?php
//-----koleksi fungsi-----
//fungsi untuk melakukan preprocessing terhadap teks
// terutama stopword removal dan stemming

function preproses($teks){
	$konek = mysqli_connect("localhost","root","","dbstbi");

	//bersihkan tanda baca, ganti dengan space
	$teks = str_replace("'", " ", $teks);
	$teks = str_replace("-", " ", $teks);
	$teks = str_replace(")", " ", $teks);
	$teks = str_replace("(", " ", $teks);
	$teks = str_replace("\"", " ", $teks);
	$teks = str_replace("/", " ", $teks);
	$teks = str_replace("=", " ", $teks);
	$teks = str_replace(".", " ", $teks);
	$teks = str_replace(",", " ", $teks);
	$teks = str_replace(":", " ", $teks);
	$teks = str_replace(";", " ", $teks);
	$teks = str_replace("!", " ", $teks);
	$teks = str_replace("?", " ", $teks);

	//ubah ke huruf kecil
	$teks = strtolower(trim($teks));

	//terapkan stopword removal
	$astoplist = array("yang", "juga", "dari", "kami", "kamu", "ini", "itu", "atau", "dan", "tersebut", "pada", "dengan", "adalah", "yaitu", "ke");

	foreach ($astoplist as $i => $value) {
		$teks = str_replace($astoplist[$i], "", $teks);
	}

	//terapkan stemming
	//buka tabel tbstem dan bandingkan dengan berita
	$query = "SELECT * FROM tbstem ORDER BY Id";
	$restem = mysqli_query($konek, $query);

	while($rowstem = mysqli_fetch_array($restem)){
		$teks = str_replace($rowstem['Term'], $rowstem['Stem'], $teks);
	}

	//kembalikan teks yang telah dipreproses
	$teks = strtolower(trim($teks));
	return $teks;
} //end function preproses
//----------
//----------

//fungsi untuk membuat index
function buatindex(){
	$konek = mysqli_connect("localhost","root","","dbstbi");

	//hapus index sebelumya
	$querycate = "TRUNCATE TABLE tbindex";
	mysqli_query($konek, $querycate);

	//ambil semua berita (teks)
	$query = "SELECT * FROM tbberita ORDER BY Id";
	$resBerita = mysqli_query($konek, $query);
	$num_rows = mysqli_num_rows($resBerita);
	print("Mengindeks sebanyak " . $num_rows . " Berita. <br />");

	while($row = mysqli_fetch_array($resBerita)){
		$docId = $row['Id'];
		$berita = $row['Berita'];

		//terapkan preprocessing
		$berita = preproses($berita);

		//simpan ke inverted index (tbindex)
		$aberita = explode(" ", trim($berita));

		foreach ($aberita as $j => $value) {
			//hanya jika term tidak null atau nim, tidak kosong
			if ($aberita[$j] != "") {
				//berapa baris hasil yang dikembalikan wuery tersebut?
				$query1 = "SELECT Count FROM tbindex WHERE Term = '$aberita[$j]' AND DocId = $docId";
				$rescount = mysqli_query($konek, $query1);
				$num_rows = mysqli_num_rows($rescount);

				//jika sudah ada DocId dan Term tersebut, naikkan count (+1)
				if ($num_rows > 0) {
					$rowcount = mysqli_fetch_array($rescount);
					$count = $rowcount['Count'];
					$count++;
					$query2 = "UPDATE tbindex SET Count =$count WHERE Term ='$aberita[$j]' AND DocId =$docId";
					mysqli_query($konek, $query2);
				}
				//jika belum ada, langsung simpan ke tbindex
				else {
					$query3 = "INSERT INTO tbindex (Term, DocId, Count) VALUES ('$aberita[$j]', $docId,1)";
					mysqli_query($konek, $query3);
				}
			} //end if
		} //end foreach
	} //end while
} // end function buatindex
buatindex()
//-----
?>