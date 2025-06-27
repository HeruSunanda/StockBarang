<?php
session_start();
//Membuat Koneksi ke Database
$conn = mysqli_connect("localhost", "root", "", "stockbarang");

//Mennambah Barang baru
if(isset($_POST['tambahbarangbaru'])){
    $namabarang = $_POST['namabarang'];
    $deskripsi = $_POST['deskripsi'];
    $stock = $_POST['stock'];


    //soal gambar
    $allowed_extension = array('png', 'jpg');
    $nama = $_FILES['file']['name'];//ngambil nama gambar
    $dot = explode('.',$nama);
    $ekstensi = strtolower(end($dot));//ngambil ekstensi gambar
    $ukuran = $_FILES['file']['size'];//ngambil ukuran gambar
    $file_tmp = $_FILES['file']['tmp_name'];//ngambil lokasi file

    //Penamaan File -> ekripsi
    $image = md5(uniqid($name, true)) . time(). '.' .$ekstensi; //Menggabungkan nama file yang diekripsi dengan ekstensinya

    //Validasi udah ada atau belum
    $cek = mysqli_query($conn, "select * from stock where namabarang='$namabarang'");
    $hitung = mysqli_num_rows($cek);

    if($hitung<1){
        //proses upload gambar
        if(in_array($ekstensi, $allowed_extension) === true ){
            //validasi Ukuran filenya
            if($ukuran < 15000000){
                move_uploaded_file($file_tmp, 'images/'.$image);
                $addtotable = mysqli_query($conn, "insert into stock (namabarang, deskripsi, stock, image) values('$namabarang', '$deskripsi', '$stock', '$image')");
                if($addtotable){
                    header('location:index.php');
                }else{
                    echo 'GAGAL mengisi Data Base';
                    header('location:index.php');
                }
            }else{
                //Kalau filenya lebih dari 15 mb
                echo '
                <script>
                    alert("File Lebij dari 15MB");
                    window.location.href="index.php"
                </script>
                ';
            }
        }else{
            //Kalau filenya tidak PNG/JPG
            echo '
            <script>
                alert("Filenya Tidak PNG/JPG");
                window.location.href="index.php"
            </script>
            ';
        }
    }else{
        //jika sudak ada
        echo '
        <script>
            alert("Nama Barang Sudah Terdaftar");
            window.location.href="index.php"
        </script>
        ';
    }

}




//Menambah barang masuk
if(isset($_POST['barangmasuk'])){
    $barangnya = $_POST['barangnya'];
    $penerima = $_POST['penerima'];
    $jumlah = $_POST['jumlah'];

    $cekstocksekarang = mysqli_query($conn, "select * from stock where idbarang='$barangnya'");
    $ambildatanya = mysqli_fetch_array($cekstocksekarang);

    $stocksekarang = $ambildatanya['stock'];
    $tambahkanstocksekarangdenganjumlah = $stocksekarang+$jumlah; 
    
    $addtomasuk = mysqli_query($conn, "insert into masuk (idbarang, keterangan, jumlah) values('$barangnya', '$penerima', '$jumlah')");
    $updatestockmasuk = mysqli_query($conn, "update stock set stock='$tambahkanstocksekarangdenganjumlah' where idbarang='$barangnya'");

    if($addtomasuk&&$updatestockmasuk){ 
        header('location:DataMasuk.php');
    }else{
        echo 'GAGAL mengisi Data Base';
        header('location:DataMasuk.php');
    }
}

//Menambah barang keluar
if(isset($_POST['addkeluarbarang'])){
    $barangnya = $_POST['barangnya'];
    $penerima = $_POST['penerima'];
    $jumlah = $_POST['jumlah'];

    $cekstocksekarang = mysqli_query($conn, "select * from stock where idbarang='$barangnya'");
    $ambildatanya = mysqli_fetch_array($cekstocksekarang);

    $stocksekarang = $ambildatanya['stock'];
    if($stocksekarang >= $jumlah){
        //Kalau barangnya cuku
        $tambahkanstocksekarangdenganjumlah = $stocksekarang-$jumlah; 
        
        $addtokeluar = mysqli_query($conn, "insert into keluar (idbarang, penerima, jumlah) values('$barangnya', '$penerima', '$jumlah')");
        $updatestockmasuk = mysqli_query($conn, "update stock set stock='$tambahkanstocksekarangdenganjumlah' where idbarang='$barangnya'");

        if($addtokeluar&&$updatestockmasuk){ 
            header('location:DataKeluar.php');
        }else{
            echo 'GAGAL mengisi Data Base';
            header('location:DataKeluar.php');
        }   
    } else{
        //kalau barangnya enggak cukup
        echo '
        <script>
            alert("Stock Saat Ini Tidak Mencukupi");
            window.location.href="DataKeluar.php";
        
        </script>
        ';
    }

}


//Update info barang
if(isset($_POST['updatebarang'])){
    $idb = $_POST['idb'];
    $namabarang = $_POST['namabarang'];
    $deskripsi = $_POST['deskripsi'];

        //soal gambar
    $allowed_extension = array('png', 'jpg');
    $nama = $_FILES['file']['name'];//ngambil nama gambar
    $dot = explode('.',$nama);
    $ekstensi = strtolower(end($dot));//ngambil ekstensi gambar
    $ukuran = $_FILES['file']['size'];//ngambil ukuran gambar
    $file_tmp = $_FILES['file']['tmp_name'];//ngambil lokasi file

    //Penamaan File -> ekripsi
    $image = md5(uniqid($name, true)) . time(). '.' .$ekstensi; //Menggabungkan nama file yang diekripsi dengan ekstensinya
    
    if($ukuran==0){
        //jika tidak ingin upload
         $update = mysqli_query($conn, "update stock set namabarang='$namabarang', deskripsi='$deskripsi' where idbarang ='$idb'");
    if($update){
         header('location:index.php');
    } else{
        echo 'GAGAL mengisi Data Base';
        header('location:index.php');
    }
    }else{
        //Jika ingin upload
        move_uploaded_file($file_tmp, 'images/'.$image);
         $update = mysqli_query($conn, "update stock set namabarang='$namabarang', deskripsi='$deskripsi', image='$image' where idbarang ='$idb'");
        if($update){
            header('location:index.php');
        } else{
            echo 'GAGAL mengisi Data Base';
            header('location:index.php');
        }
    }
}


//Menghapus barang dari stock
if(isset($_POST['hapusbarang'])){
    $idb = $_POST['idb'];

    $gambar = mysqli_query($conn, "select * from stock where idbarang='$idb'");
    $get = mysqli_fetch_array($gambar);
    $img = 'images/' .$get['image'];
    unlink($img); 

    $hapus = mysqli_query($conn, "delete from stock where idbarang='$idb'");
    if($hapus){
         header('location:index.php');
    } else{
        echo 'GAGAL mengisi Data Base';
        header('location:index.php');
    }
}



//mengubah data barang masuk
if(isset($_POST['updatebarangmasuk'])){
    $idb = $_POST['idb'];
    $idm = $_POST['idm'];
    $deskripsi = $_POST['keterangan'];
    $jumlah = $_POST['jumlah'];

    $lihatstock = mysqli_query($conn, "select * from stock where idbarang='$idb'");
    $stocknya = mysqli_fetch_array($lihatstock);
    $stockskrng = $stocknya['stock'];

    $jumlahskrng = mysqli_query($conn, "select * from masuk where idmasuk='$idm'");
    $jumlahnya = mysqli_fetch_array($jumlahskrng);
    $jumlahskrng = $jumlahnya['jumlah'];


    if($jumlah>$jumlahskrng){
        $selisih = $jumlah-$jumlahskrng;
        $kurangin = $stockskrng + $selisih;
        $kurangistocknya = mysqli_query($conn, "update stock set stock='$kurangin' where idbarang='$idb'");
        $updatenya = mysqli_query($conn, "update masuk set jumlah='$jumlah', keterangan='$deskripsi' where idmasuk='$idm'");
            if($kurangistocknya&&$updatenya){
                header('location:DataMasuk.php');
                } else{
                    echo 'GAGAL mengisi Data Base';
                    header('location:DataMasuk.php');
            }
    }else {
        $selisih = $jumlahskrng-$jumlah;
        $kurangin = $stockskrng - $selisih;
        $kurangistocknya = mysqli_query($conn, "update stock set stock='$kurangin' where idbarang='$idb'");
        $updatenya = mysqli_query($conn, "update masuk set jumlah='$jumlah', keterangan='$deskripsi' where idmasuk='$idm'");
            if($kurangistocknya&&$updatenya){
                header('location:DataMasuk.php');
                } else{
                    echo 'GAGAL mengisi Data Base';
                    header('location:DataMasuk.php');
            }

    }
}



//menghapus barang masuk
if(isset($_POST['hapusbarangmasuk'])){
    $idb = $_POST['idb'];
    $jumlah = $_POST['jlh'];
    $idm = $_POST['idm'];

    $getdatastock = mysqli_query($conn, "select * from stock where idbarang='$idb'");
    $data = mysqli_fetch_array($getdatastock);
    $stock = $data['stock'];

    $selisih = $stock-$jumlah;

    $update =mysqli_query($conn, "update stock set stock='$selisih' where idbarang='$idb'");
    $hapusdata = mysqli_query($conn, "delete from masuk where idmasuk='$idm'");

    if($update&&$hapusdata){
        header('location: DataMasuk.php');
    } else{
         header('location: DataMasuk.php');
    }
}


//Mengubah data barang keluar

//mengedit data barang keluar
if(isset($_POST['updatebarangkeluar'])){
    $idb = $_POST['idb'];
    $idk = $_POST['idk'];
    $penerima = $_POST['penerima']; 
    $jumlah = $_POST['jumlah'];

    $lihatstock = mysqli_query($conn, "select * from stock where idbarang='$idb'");
    $stocknya = mysqli_fetch_array($lihatstock);
    $stockskrng = $stocknya['stock'];

    $jumlahskrng = mysqli_query($conn, "select * from keluar where idkeluar='$idk'");
    $jumlahnya = mysqli_fetch_array($jumlahskrng);
    $jumlahskrng = $jumlahnya['jumlah'];


    if($jumlah>$jumlahskrng){
        $selisih = $jumlah-$jumlahskrng;
        $kurangin = $stockskrng - $selisih;
        $kurangistocknya = mysqli_query($conn, "update stock set stock='$kurangin' where idbarang='$idb'");
        $updatenya = mysqli_query($conn, "update keluar set jumlah='$jumlah', penerima='$penerima' where idkeluar='$idk'");
            if($kurangistocknya&&$updatenya){
                header('location:DataKeluar.php');
                } else{
                    echo 'GAGAL mengisi Data Base';
                    header('location:DataKeluar.php');
            }
    }else {
        $selisih = $jumlahskrng-$jumlah;
        $kurangin = $stockskrng + $selisih;
        $kurangistocknya = mysqli_query($conn, "update stock set stock='$kurangin' where idbarang='$idb'");
        $updatenya = mysqli_query($conn, "update keluar set jumlah='$jumlah', penerima='$penerima' where idkeluar='$idk'");
            if($kurangistocknya&&$updatenya){
                header('location:DataKeluar.php');
                } else{
                    echo 'GAGAL mengisi Data Base';
                    header('location:DataKeluar.php');
            }

    }
}



//menghapus barang keluar
if(isset($_POST['hapusbarangkeluar'])){
    $idb = $_POST['idb'];
    $jumlah = $_POST['jlh'];
    $idk = $_POST['idk'];

    $getdatastock = mysqli_query($conn, "select * from stock where idbarang='$idb'");
    $data = mysqli_fetch_array($getdatastock);
    $stock = $data['stock'];

    $selisih = $stock+$jumlah;

    $update =mysqli_query($conn, "update stock set stock='$selisih' where idbarang='$idb'");
    $hapusdata = mysqli_query($conn, "delete from keluar where idkeluar='$idk'");

    if($update&&$hapusdata){
        header('location: DataKeluar.php');
    } else{
         header('location: DataKeluar.php');
    }
}


//Tambah Admin 
if(isset($_POST['addadmin'])){
    $email = $_POST['email'];
    $password = $_POST['password'];

    $quearyinsert = mysqli_query($conn, "insert into login (email, password) values ('$email','$password')");

    if($quearyinsert){
        header('location:admin.php'); 
    } else{
        header('location:admin.php'); 
    }
}

//Update Admin
if(isset($_POST['updateadmin'])){
    $emailbaru = $_POST['emailadmin'];
    $passwordbaru = $_POST['passwordbaru'];
    $idnya = $_POST['id'];

    $quearyupdate = mysqli_query($conn, "update login set email='$emailbaru', password='$passwordbaru' where iduser='$idnya'");

    if($quearyupdate){
        header('location:admin.php');
    } else{
        header('location:admin.php');
    }
}



//hapus Admin
if(isset($_POST['hapusadmin'])){
    $id = $_POST['id'];
    
    $querydelete = mysqli_query($conn, "delete from login where iduser='$id'");

    if($querydelete){
        header('location:admin.php');
    } else{
        header('location:admin.php');
    }
}
?> 