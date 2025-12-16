<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asisten Pembayaran Baitul Ilmi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Gaya dasar untuk membedakan pesan */
        .chat-box {
            height: 500px;
            overflow-y: scroll;
            border: 1px solid #dee2e6;
            background-color: #f8f9fa;
        }
        .message {
            padding: 10px;
            border-radius: 15px;
            margin-bottom: 10px;
            max-width: 80%;
        }
        .user-message {
            background-color: #0d6efd; /* Biru */
            color: white;
            margin-left: auto;
        }
        .ai-message {
            background-color: #e9ecef; /* Abu-abu muda */
            color: #212529;
            margin-right: auto;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">🤖 Asisten Pembayaran Digital Baitul Ilmi</h2>

        <div id="chat-box" class="chat-box p-3">
            <div class="message ai-message">
                Halo, saya Asisten Pembayaran Baitul Ilmi. Silakan ketik pertanyaan Anda seputar tagihan, cara bayar, atau status transaksi.
            </div>
        </div>

        <form id="chat-form" class="mt-3">
            @csrf <div class="input-group">
                <input type="text" id="user-message" class="form-control" placeholder="Tanyakan tentang cara bayar, tagihan, atau Midtrans..." required>
                <button type="submit" class="btn btn-success" id="send-button">Kirim</button>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('chat-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const messageInput = document.getElementById('user-message');
            const sendButton = document.getElementById('send-button');
            const userMessage = messageInput.value.trim();
            const chatBox = document.getElementById('chat-box');

            if (userMessage === '') return;

            // 1. Tampilkan pesan pengguna
            appendMessage(userMessage, 'user'); 
            messageInput.value = ''; // Kosongkan input
            
            // Nonaktifkan input dan tombol saat menunggu respon
            sendButton.disabled = true;
            messageInput.disabled = true;

            // 2. Tampilkan loading indicator
            const loadingId = appendLoading();

            try {
                // 3. Kirim permintaan ke Laravel Controller (route: chatbot.send)
                const response = await fetch("{{ route('chatbot.send') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        // Ambil token CSRF dari input tersembunyi
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value 
                    },
                    body: JSON.stringify({ message: userMessage })
                });

                const data = await response.json();
                
                // 4. Hapus loading dan tampilkan balasan
                removeLoading(loadingId);

                if (data.reply) {
                    appendMessage(data.reply, 'ai');
                } else if (data.error) {
                    appendMessage(`Error: ${data.error}`, 'ai');
                } else {
                    appendMessage('Maaf, respons tidak valid dari server.', 'ai');
                }

            } catch (error) {
                removeLoading(loadingId);
                console.error('Fetch error:', error);
                appendMessage('Terjadi kesalahan koneksi jaringan.', 'ai');
            } finally {
                // Aktifkan kembali input dan tombol
                sendButton.disabled = false;
                messageInput.disabled = false;
            }
        });

        // Helper function: Menambahkan pesan
        function appendMessage(text, sender) {
            const chatBox = document.getElementById('chat-box');
            const messageDiv = document.createElement('div');
            
            messageDiv.classList.add('message', sender === 'user' ? 'user-message' : 'ai-message');
            messageDiv.innerHTML = text; // Menggunakan innerHTML untuk mendukung markdown/formatting dari Gemini
            
            chatBox.appendChild(messageDiv);
            chatBox.scrollTop = chatBox.scrollHeight; // Scroll ke bawah otomatis
        }

        // Helper function: Menambahkan loading
        function appendLoading() {
            const chatBox = document.getElementById('chat-box');
            const loadingDiv = document.createElement('div');
            const uniqueId = 'loading-' + Date.now();
            
            loadingDiv.id = uniqueId;
            loadingDiv.classList.add('ai-message', 'message');
            loadingDiv.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>AI sedang memproses...';
            
            chatBox.appendChild(loadingDiv);
            chatBox.scrollTop = chatBox.scrollHeight;
            return uniqueId;
        }

        // Helper function: Menghapus loading
        function removeLoading(id) {
            const loadingDiv = document.getElementById(id);
            if (loadingDiv) {
                loadingDiv.remove();
            }
        }
    </script>
</body>
</html>