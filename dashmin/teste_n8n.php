<!-- Widget container -->
<div id="gg-help-widget-root"></div>

<!-- Marked.js para Markdown -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

<style>
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

  * { box-sizing: border-box; }

  body {
    font-family: 'Poppins', sans-serif !important;
  }

  #gg-help-button {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 54px;
    height: 54px;
    border-radius: 50%;
    background: #652cb1;
    color: white;
    font-size: 22px;
    border: none;
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 99999;
    box-shadow: 0 0 12px rgba(101, 44, 177, 0.5);
    cursor: pointer;
    transition: box-shadow 0.3s ease;
  }

  #gg-help-button.blink {
    animation: blink 1s infinite;
  }

  @keyframes blink {
    0%, 100% { box-shadow: 0 0 12px rgba(101, 44, 177, 0.5); }
    50% { box-shadow: 0 0 20px rgba(255, 255, 255, 0.8); }
  }

  #gg-help-chat {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 360px;
    height: 520px;
    background: #0e0d1b;
    border-radius: 16px;
    box-shadow: 0 0 20px rgba(101, 44, 177, 0.6);
    display: none;
    flex-direction: column;
    overflow: hidden;
    z-index: 99999;
    animation: slideUp 0.3s ease-out;
    font-family: 'Poppins', sans-serif;
  }

  @keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }

  @keyframes slideDown {
    from { opacity: 1; transform: translateY(0); }
    to { opacity: 0; transform: translateY(20px); }
  }

  .gg-help-hide {
    animation: slideDown 0.3s ease-in forwards;
  }

  #gg-help-header {
    background: #652cb1;
    color: white;
    padding: 16px;
    font-weight: 600;
    font-size: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  #gg-help-body {
    flex: 1;
    background: #141324;
    padding: 20px;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
  }

  .gg-help-msg-bot {
    background: #652cb1;
    color: white;
    align-self: flex-start;
    padding: 10px 14px;
    border-radius: 16px 16px 16px 6px;
    margin-bottom: 10px;
    max-width: 80%;
    font-size: 14px;
  }

  .gg-help-msg-user {
    background: #2c2b3e;
    color: #f1f1f1;
    align-self: flex-end;
    padding: 10px 14px;
    border-radius: 16px 16px 6px 16px;
    margin-bottom: 10px;
    max-width: 80%;
    font-size: 14px;
  }

  .gg-help-msg-bot strong {
    font-weight: 600;
    color: #ffffff;
  }

  .gg-help-msg-bot em {
    font-style: italic;
    color: #cccccc;
  }

  .gg-help-msg-bot ul,
  .gg-help-msg-bot ol {
    padding-left: 20px;
    margin: 10px 0;
  }

  .gg-help-msg-bot a {
    color: #fff;
    text-decoration: underline;
  }

  .gg-help-typing {
    font-size: 13px;
    padding: 10px 14px;
    background: #34314d;
    color: #ddd;
    border-radius: 12px;
    max-width: fit-content;
    align-self: flex-start;
    margin-bottom: 10px;
    opacity: 0.75;
    font-style: italic;
  }

  .dot-loader span {
    animation: blinkDots 1.4s infinite;
    display: inline-block;
  }

  .dot-loader span:nth-child(2) { animation-delay: 0.2s; }
  .dot-loader span:nth-child(3) { animation-delay: 0.4s; }

  @keyframes blinkDots {
    0%, 100% { opacity: 0.2; }
    50% { opacity: 1; }
  }

  #gg-help-footer {
    padding: 14px;
    background: #0e0d1b;
    display: flex;
    gap: 10px;
    border-top: 1px solid #2a2740;
  }

  #gg-help-input {
    flex: 1;
    padding: 10px;
    border: 1px solid #3f3c5c;
    border-radius: 8px;
    background: #1c1b30;
    color: #e1e1e6;
    outline: none;
  }

  #gg-help-send {
    background: #652cb1;
    color: white;
    border: none;
    padding: 10px 18px;
    border-radius: 8px;
    cursor: pointer;
  }

  .gg-help-close {
    background: none;
    border: none;
    color: white;
    font-size: 20px;
    cursor: pointer;
  }
</style>

<script>
  const root = document.getElementById("gg-help-widget-root");

  root.innerHTML = `
    <button id="gg-help-button">🔥</button>
    <div id="gg-help-chat">
      <div id="gg-help-header">
          🥷🏻 Gg Aula Assistant
        <button class="gg-help-close" onclick="ggMinimizeChat()">—</button>
      </div>
      <div id="gg-help-body">
        <div class="gg-help-msg-bot"><strong>Oi! 👋 Como posso te ajudar?</strong></div>
      </div>
      <div id="gg-help-footer">
        <input type="text" id="gg-help-input" placeholder="Digite sua mensagem..." />
        <button id="gg-help-send">Enviar</button>
      </div>
    </div>
  `;

  const chatBox = document.getElementById("gg-help-chat");
  const toggleButton = document.getElementById("gg-help-button");

  toggleButton.addEventListener("click", () => {
    chatBox.style.display = "flex";
    toggleButton.style.display = "none";
    toggleButton.classList.remove("blink");
  });

  function ggMinimizeChat() {
    chatBox.classList.add("gg-help-hide");
    setTimeout(() => {
      chatBox.style.display = "none";
      chatBox.classList.remove("gg-help-hide");
      toggleButton.style.display = "flex";
    }, 300);
  }

  const scrollToBottom = () => {
    const lastMsg = document.querySelector("#gg-help-body > div:last-child");
    if (lastMsg) lastMsg.scrollIntoView({ behavior: "smooth" });
  };

  const sendMessage = () => {
    const input = document.getElementById("gg-help-input");
    const message = input.value.trim();
    if (!message) return;

    const body = document.getElementById("gg-help-body");

    const userMsg = document.createElement("div");
    userMsg.className = "gg-help-msg-user";
    userMsg.textContent = message;
    body.appendChild(userMsg);
    scrollToBottom();

    const typingIndicator = document.createElement("div");
    typingIndicator.className = "gg-help-typing dot-loader";
    typingIndicator.innerHTML = `Pensando<span>.</span><span>.</span><span>.</span>`;
    body.appendChild(typingIndicator);
    scrollToBottom();

    const chatId = sessionStorage.getItem("chatId") || "chat_" + Math.random().toString(36).substr(2, 9);
    sessionStorage.setItem("chatId", chatId);

    fetch('http://localhost:5678/webhook/a84b31a6-f116-4417-a5b7-98a3eb4e991a/chat', {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        chatId: chatId,
        message: message,
        route: "general"
      })
    })
    .then(res => res.json())
    .then(data => {
      setTimeout(() => {
        typingIndicator.remove();
        const botMsg = document.createElement("div");
        botMsg.className = "gg-help-msg-bot";
        botMsg.innerHTML = marked.parse(data.output || "Desculpe, não entendi.");
        body.appendChild(botMsg);
        scrollToBottom();
        if (chatBox.style.display === "none") {
          toggleButton.classList.add("blink");
        }
      }, 1000);
    })
    .catch(err => {
      typingIndicator.remove();
      console.error("Erro:", err);
    });

    input.value = "";
  };

  document.getElementById("gg-help-send").addEventListener("click", sendMessage);
  document.getElementById("gg-help-input").addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
      e.preventDefault();
      sendMessage();
    }
  });
</script>

