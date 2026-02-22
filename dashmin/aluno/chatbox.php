<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Input estilo ChatGPT + n8n</title>

  <style>


    /* Container fixo no rodapé */
    .chat-input-container {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      padding: 12px;
    }

    .chat-input-box {
      display: flex;
      align-items: flex-end;
      gap: 8px;
      max-width: 500px;
      margin: 0 auto;
      margin-bottom: 15px;
      background: #fff;
      border: 1px solid #ddd;
      border-radius: 14px;
      padding: 10px;
    }

    /* Botão + */
    .plus-btn {
      border: none;
      background: transparent;
      font-size: 22px;
      cursor: pointer;
      padding: 4px 6px;
    }

    .plus-btn:hover {
      opacity: 0.7;
    }

    /* Textarea que cresce */
    textarea {
      flex: 1;
      resize: none;
      border: none;
      outline: none;
      font-size: 16px;
      line-height: 1.4;
      max-height: 200px;
      overflow-y: auto;
    }

    /* Botão enviar */
    .send-btn {
      border: none;
      background: #eaeaea;
      border-radius: 50%;
      width: 38px;
      height: 38px;
      cursor: pointer;
      font-size: 16px;
    }

    .send-btn:hover {
      background: #ddd;
    }
  </style>
</head>

<body>

  <!-- Input fixo no rodapé -->
  <div class="chat-input-container">
    <div class="chat-input-box">

      <button class="plus-btn" onclick="openMoreOptions()">＋</button>

      <textarea
        id="userInput"
        placeholder="Faça uma pergunta..."
        rows="1"
      ></textarea>

      <button class="send-btn" onclick="sendToWebhook()">➤</button>
    </div>
  </div>

  <script>
    const textarea = document.getElementById("userInput");

    // Auto crescer conforme texto
    textarea.addEventListener("input", () => {
      textarea.style.height = "auto";
      textarea.style.height = textarea.scrollHeight + "px";
    });

    function sendToWebhook() {
      const text = textarea.value.trim();
      if (!text) return;

      fetch("https://SEU_WEBHOOK_N8N_AQUI", {
        method: "POST",
        headers: {
          "Content-Type": "application/json"
        },
        body: JSON.stringify({
          message: text
        })
      })
      .then(res => res.json())
      .then(data => {
        console.log("Resposta do n8n:", data);
        textarea.value = "";
        textarea.style.height = "auto";
      })
      .catch(err => console.error(err));
    }

    // Enter envia / Shift+Enter quebra linha
    textarea.addEventListener("keydown", (e) => {
      if (e.key === "Enter" && !e.shiftKey) {
        e.preventDefault();
        sendToWebhook();
      }
    });

    function openMoreOptions() {
      alert("Aqui você pode abrir menu: anexos, imagens, etc.");
    }
  </script>

</body>
</html>
