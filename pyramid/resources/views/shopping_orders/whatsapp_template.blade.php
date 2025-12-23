<h2 class="card-title mb-2">To Driver:</h2>
<div class="position-relative">
<pre id="driver-message">
Order
Nama : {{ $order->customer_name }}
No WA : {{ $order->customer_phone }}
Layanan : Belanja-Aja!
Lokasi Belanja : {{ $order->pickup_location }}
Lokasi Drop : {{ $order->drop_location }}

Bang WA Tolong Cari daftar ini
{{ $order->shopping_list }}
</pre>
    <button class="btn btn-outline-success position-absolute mt-2 mr-2" style="top: 0; right: 0; width: 80px;"
            onclick="navigator.clipboard.writeText($('#driver-message').html());">COPY
    </button>
</div>

<h2 class="card-title mb-2 mt-4">To Customer:</h2>
<div class="position-relative">

<pre id="customer-message">
Hai Kak, Kami ingin Konfirmasi pesanan beserta harga barang belanja Kakak.

Kami Akan Memprosesnya

Nama Pemesan: {{ $order->customer_name }}
Nomor WA: {{ $order->customer_phone }}
Driver: {{ $order->driver ? $order->driver->name : '' }}

Daftar Pesanan
{{ $order->shopping_list }}

TOTAL HARGA:  Rp {{ display_price($order->subtotal) }}
Biaya Layanan Belanja-Aja!: Rp {{ display_price($order->service_fee) }}
Ongkos kirim: Rp {{ display_price($order->delivery_fee) }}
Total Bayar:  Rp {{ display_price($order->total) }}

Harga di atas belum termasuk biaya parkir  (Jika Ada)

Pilih Pembayaran :
1. Cash
2. Transfer (BNI/1845819261/WAAJA TECH SOLUTION)

Setelah melakukan pembayaran, mohon sertakan buktinya, agar pesanan di proses
</pre>
    <button class="btn btn-outline-success position-absolute mt-2 mr-2" style="top: 0; right: 0; width: 80px;"
            onclick="navigator.clipboard.writeText($('#customer-message').html());">COPY
    </button>
</div>
