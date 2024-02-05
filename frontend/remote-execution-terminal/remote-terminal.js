export class RemoteTerminal {
    constructor(containerId, websocketUrl) {
        this.containerId = containerId;
        this.websocketUrl = websocketUrl;

        this.terminal = new Terminal({
            cursorBlink: true,
            fontFamily: '"Fira Code", monospace',
            fontSize: 20,
            theme: {
                background: '#0D0D0D',
                foreground: '#33FF00',
                cursor: '#33FF00',
                selection: 'rgba(51, 255, 0, 0.3)',
            }
        });

        this.socket = new WebSocket(this.websocketUrl);
        this.commandBuffer = '';

        this.initTerminal();
        this.setupWebSocket();
    }

    initTerminal() {
        this.terminal.open(document.getElementById(this.containerId));
        this.terminal.writeln('Connecting to the server...');
        this.terminal.onKey(key => this.handleTerminalKey(key));
    }

    setupWebSocket() {
        this.socket.onopen = () => this.terminal.writeln('Connected to the server.\r\n');
        this.socket.onmessage = event => this.terminal.write(event.data);
        this.socket.onclose = () => this.terminal.writeln('\r\nConnection closed.');
        this.socket.onerror = error => this.terminal.writeln(`\r\nConnection error: ${error.message}`);
    }

    handleTerminalKey(key) {
        const char = key.domEvent.key;

        if (char === 'Enter') {
            this.socket.send(this.commandBuffer + '\n');
            this.terminal.write('\r\n');
            this.commandBuffer = '';
        } else if (char === 'Backspace') {
            if (this.commandBuffer.length > 0) {
                this.commandBuffer = this.commandBuffer.substring(0, this.commandBuffer.length - 1);
                this.terminal.write('\b \b');
            }
        } else {
            this.commandBuffer += char;
            this.terminal.write(char);
        }
    }
}
