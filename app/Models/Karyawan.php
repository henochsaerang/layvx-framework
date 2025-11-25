<?php
namespace App\Models;
use App\Core\Model;

class Karyawan extends Model {
    protected static $table = 'karyawan'; // Asumsi nama tabel adalah 'karyawan'
    protected static $primaryKey = 'id_karyawan'; // Contoh jika PK bukan 'id'
    protected static $fillable = ['nama_karyawan', 'email', 'posisi'];
}
