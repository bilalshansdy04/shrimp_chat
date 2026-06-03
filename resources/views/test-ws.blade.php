<!DOCTYPE html>
<html>
<head>
    <title>Uji Coba Real-Time ShrimpChat</title>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    
    <script>
        // Nyalakan log di console browser agar terlihat proses koneksinya
        Pusher.logToConsole = true;

        // Inisialisasi koneksi ke server Laravel Reverb kamu
        var pusher = new Pusher('{{ env('REVERB_APP_KEY') }}', {
            wsHost: '127.0.0.1',
            wsPort: 8080,
            forceTLS: false, // Karena kita pakai localhost (bukan https)
            disableStats: true,
            enabledTransports: ['ws', 'wss'],
            cluster: 'mt1' // Dummy cluster
        });

        // Berlangganan (Subscribe) ke saluran 'room.1'
        var channel = pusher.subscribe('room.1');
        
        // Dengarkan event bernama 'message.new'
        channel.bind('message.new', function(data) {
            // Jika ada pesan masuk, munculkan Pop-up Alert dan tulis di HTML!
            alert('PESAN BARU DARI POSTMAN: ' + data.message.content);
            
            let p = document.createElement("p");
            p.innerHTML = "<b>Pesan Baru:</b> " + data.message.content;
            document.body.appendChild(p);
        });
    </script>
</head>
<body style="font-family: sans-serif; padding: 20px;">
    <h1>Radar ShrimpChat 📡</h1>
    <p>Menunggu pesan terbang dari Postman ke Room 1...</p>
    <hr>
</body>
</html>