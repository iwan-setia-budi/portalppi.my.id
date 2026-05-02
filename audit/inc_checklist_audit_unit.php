<?php
/**
 * Daftar tilik Audit Unit — kode_bagian per sub bab (A01–A13), urutan_item per baris dalam sub bab.
 * Kode tampilan item = kode_bagian . str_pad(urutan_item, 2, '0', STR_PAD_LEFT) → mis. A0101.
 */
return [
  'A01' => [
    'title' => 'Audit Fasilitas Kebersihan Tangan',
    'items' => [
      'Tersedia sabun cuci tangan/handrub',
      'Wastafel bebas dari peralatan yang tidak tepat',
      'Fasilitas kebersihan tangan bersih',
      'Tersedia tempat sampah dekat wastafel',
      'Tersedia poster kebersihan tangan',
      'handrub dan sabun cuci tangan diberi tanggal buka dan tanggal expired',
      'Staf memahami langkah kebersihan tangan sesuai standar',
    ],
  ],
  'A02' => [
    'title' => 'Audit Pengelolaan Limbah Benda Tajam',
    'items' => [
      'Terbebas dari sampah infeksius dan non infeksius',
      'Wadah limbah tajam diletakan ditempat aman',
      'Ada tanggal penggunaan sharp container',
      'Tidak ada benda tajam yang keluar dari wadah',
      'Limbah tajam langsung dibuang ke wadah limbah tajam',
      'Isi sharp container tidak melebihi garis batas sharp container',
      'Staf memahami alur penanganan bila tertusuk benda tajam',
    ],
  ],
  'A03' => [
    'title' => 'Audit Pengelolaan Limbah Cair',
    'items' => [
      'Sisa cairan infus dibuang kedalam wastafel atau zink',
      'Sisa obat cair dibuang ke dalama wastafel atau zink',
      'Limbah cair (urin, faeces) segera dibuang ke tempat pembuangan (wc/ disposal)',
      'Wadah limbah cair (urinal/ pispot) dilakukan pencucian setelah digunakan',
      'Terdapat bukti pelaksanaan monitoring ipal',
    ],
  ],
  'A04' => [
    'title' => 'Audit Pengelolaan Limbah Cairan Tubuh',
    'items' => [
      'Limbah darah/ komponen darah di buang di limbah medis',
      'Petugas sewaktu menangani limbah darah/ komponen darah menggunakan APD',
      'Petugas memahami pengelolaan limbah darah/ komponen darah',
      'Ada formulir pembuangan sisa darah/ komponen darah di laboratorium',
    ],
  ],
  'A05' => [
    'title' => 'Audit Pengelolaan Limbah Infeksius',
    'items' => [
      'Tempat sampah menggunakan Pedal dan berfungsi baik',
      'Kondisi tempat sampah dalam keadaan bersih dan tertutup',
      'Tempat sampah infeksius menggunakan kantong plastic kuning',
      'Limbah dibuang setelah 3/4 penuh',
      'Sampah dibuang sesuai dengan jenisnya',
      'Staf memahami pengelolaan limbah sesuai dengan prosedur',
      'Limbah Jaringan tubuh disimpan dalam wadah khusus',
    ],
  ],
  'A06' => [
    'title' => 'Audit Pengelolaan Limbah Non Infeksius',
    'items' => [
      'Tempat sampah menggunakan Pedal dan berfungsi baik',
      'Kondisi dalam keadaan bersih dan tertutup',
      'Sampah dibuang sesuai dengan jenisnya',
      'Tempat sampah non infeksius menggunakan kantong hitam',
      'Limbah dibuang setelah 3/4 penuh',
    ],
  ],
  'A07' => [
    'title' => 'Audit Pengelolaan Linen di Ruangan',
    'items' => [
      'Tersedia kantong untuk linen infeksius dan non infeksius',
      'Linen bersih di tempatkan pada lemari kering, bersih dan tertutup',
      'Linen kotor non infeksius ditempatkan pada kantong hitam dalam wadah tertutup',
      'Linen kotor infeksius ditempatkan pada kantong kuning dalam wadah tertutup',
      'Jika terdapat feses atau darah, buang terlebih dahulu sebelum dimasukan ke plastik infeksius',
      'Tidak meletakan linen di lantai',
      'Tidak menyeret linen',
      'Tidak meletakan linen dimeja dan kursi',
      'Tidak mengibaskan linen kotor',
      'Linen tidak bau, tidak lembab dan tidak ada noda',
      'Pemisahan linen yang bersih dan kotor',
    ],
  ],
  'A08' => [
    'title' => 'Audit Kebersihan Lingkungan',
    'items' => [
      'Pembersihan ruangan dilakukan secara rutin dan terjadwal',
      'Pembersihan ruangan dilakukan setelah pergantian pasien',
      'Ruangan, dinding, atap dan lampu bebas dari sarang laba-laba',
      'Ruangan bebas dari serangga, kecoa, semut dan nyamuk',
      'Tidak terdapat water intrution/ jamur',
      'Permukaan horizontal tidak ada debu',
      'Petugas memahami prosedur pembersihan ruangan',
      'Pengenceran desinfektan mengikuti petunjuk dari pabrik',
      'Ada ceklist pembersihan harian area bermain anak',
      'Tirai Gorden dalam keadaan bersih',
      'Ada jadwal pembersihan gorden secara rutin',
    ],
  ],
  'A09' => [
    'title' => 'Audit Penanganan Tumpahan',
    'items' => [
      'Staf memahami Prosedure Penanganan Tumpahan Cairan Tubuh',
      'Spill Kit Diletakan Ditempat yang mudah diambil',
      'Isi Spill Kit Lengkap',
    ],
  ],
  'A10' => [
    'title' => 'Audit Penyuntikan yang Aman',
    'items' => [
      'Kulkas tempat menyimpan obat terlihat bersih',
      'Tidak ada makanan/minuman didalam kulkas obat',
      'Tidak ada bunga es dalam kulkas',
      'Vial/obat multidose diberi nama, tanggal, dan jam buka',
      'Suhu kulkas tempat penyimpanan obat (2-8°C)',
      'Monitoring suhu kulkas dan ruangan diisi setiap hari',
      'Teknik aseptik dilakukan',
      'Kebersihan tempat peracikan',
      'Desinfeksi vial dilakukan',
      'Obat disimpan pada tempatnya',
    ],
  ],
  'A11' => [
    'title' => 'Audit Perawatan Peralatan Pasien',
    'items' => [
      'Petugas Tahu Penanganan Instrument Kotor',
      'Alat Medis di bersihkan secara rutin (terdapat bukti monitoring pembersihan alat medis)',
      'Instrument Steril disusun berdasarkan FIFO',
      'Instrument Steril diletakan dalam tempat yang bersih dan kering serta ada pengontrolan suhu dan kelembaban',
      'Kemasan bahan packing tidak terbuka, tidak berlubang, tidak basah dan tidak robek',
      'Pada Label barang tidak melebihi batas ahkir tanggal exp date yang tercantum',
      'Wadah alat instrumen bersih dan kotor terpisah, kondisi bersih',
      'Instrumen yang sudah steril bebas dari sisa kotoran yang menempel',
    ],
  ],
  'A12' => [
    'title' => 'Audit BSC / LAF',
    'items' => [
      'Area kerja dibersihkan dengan desinfectan sebelum dan sesudah tindakan',
      'Permukaan interior bersih',
      'Ada bukti bahwa alat diuji secara teratur dan didokumentasikan',
    ],
  ],
  'A13' => [
    'title' => 'Audit Pembuangan Darah dan Komponennya',
    'items' => [
      'Limbah darah dibuang ke spoel hok / tempat sampah infeksius',
      'Alat yang terkena darah langsung dibersihkan',
    ],
  ],
];
