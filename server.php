<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

class CS16ServerQuery {
    private $ip;
    private $port;
    private $timeout;
    private $socket;
    
    public function __construct($ip, $port, $timeout = 3) {
        $this->ip = $ip;
        $this->port = $port;
        $this->timeout = $timeout;
    }
    
    /**
     * Ana query fonksiyonu - sunucu bilgilerini getirir
     */
    public function getServerInfo() {
        try {
            $this->connect();
            
            // A2S_INFO query paketi gönder
            $packet = "\xFF\xFF\xFF\xFF\x54Source Engine Query\x00";
            $this->sendPacket($packet);
            
            // Yanıtı al
            $response = $this->receivePacket();
            
            if (!$response) {
                throw new Exception("Sunucudan yanıt alınamadı");
            }
            
            $info = $this->parseServerInfo($response);
            
            // Oyuncu listesini al
            $players = $this->getPlayers();
            
            $this->disconnect();
            
            return [
                'success' => true,
                'server_info' => $info,
                'players' => $players,
                'query_time' => microtime(true),
                'ping' => $this->calculatePing()
            ];
            
        } catch (Exception $e) {
            $this->disconnect();
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'error_code' => 'CONNECTION_FAILED'
            ];
        }
    }
    
    /**
     * Oyuncu listesini getirir
     */
    private function getPlayers() {
        try {
            // A2S_PLAYER query için önce challenge al
            $challengePacket = "\xFF\xFF\xFF\xFF\x55\xFF\xFF\xFF\xFF";
            $this->sendPacket($challengePacket);
            
            $challengeResponse = $this->receivePacket();
            if (!$challengeResponse) {
                return [];
            }
            
            // Challenge kodunu çıkar
            $challenge = substr($challengeResponse, 5, 4);
            
            // Oyuncu listesi query'si gönder
            $playerPacket = "\xFF\xFF\xFF\xFF\x55" . $challenge;
            $this->sendPacket($playerPacket);
            
            $playerResponse = $this->receivePacket();
            if (!$playerResponse) {
                return [];
            }
            
            return $this->parsePlayerInfo($playerResponse);
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Sunucu bilgilerini parse eder
     */
    private function parseServerInfo($data) {
        $pos = 4; // İlk 4 byte header
        $header = ord($data[$pos++]);
        
        if ($header !== 0x49) { // 'I' header
            throw new Exception("Geçersiz sunucu yanıtı");
        }
        
        $protocol = ord($data[$pos++]);
        
        // Sunucu adını al
        $serverName = '';
        while ($pos < strlen($data) && $data[$pos] !== "\x00") {
            $serverName .= $data[$pos++];
        }
        $pos++; // null terminator
        
        // Harita adını al
        $mapName = '';
        while ($pos < strlen($data) && $data[$pos] !== "\x00") {
            $mapName .= $data[$pos++];
        }
        $pos++; // null terminator
        
        // Folder adını al
        $folder = '';
        while ($pos < strlen($data) && $data[$pos] !== "\x00") {
            $folder .= $data[$pos++];
        }
        $pos++; // null terminator
        
        // Game adını al
        $game = '';
        while ($pos < strlen($data) && $data[$pos] !== "\x00") {
            $game .= $data[$pos++];
        }
        $pos++; // null terminator
        
        // App ID (2 bytes)
        $appId = unpack('v', substr($data, $pos, 2))[1];
        $pos += 2;
        
        // Oyuncu sayıları
        $players = ord($data[$pos++]);
        $maxPlayers = ord($data[$pos++]);
        $bots = ord($data[$pos++]);
        
        // Server type
        $serverType = $data[$pos++];
        $environment = $data[$pos++];
        $visibility = ord($data[$pos++]);
        $vac = ord($data[$pos++]);
        
        return [
            'name' => $this->cleanString($serverName),
            'map' => $this->cleanString($mapName),
            'folder' => $this->cleanString($folder),
            'game' => $this->cleanString($game),
            'app_id' => $appId,
            'players' => $players,
            'max_players' => $maxPlayers,
            'bots' => $bots,
            'server_type' => $serverType,
            'environment' => $environment,
            'visibility' => $visibility,
            'vac_secured' => $vac === 1,
            'protocol' => $protocol
        ];
    }
    
    /**
     * Oyuncu bilgilerini parse eder
     */
    private function parsePlayerInfo($data) {
        $pos = 4; // Header
        $header = ord($data[$pos++]);
        
        if ($header !== 0x44) { // 'D' header
            return [];
        }
        
        $playerCount = ord($data[$pos++]);
        $players = [];
        
        for ($i = 0; $i < $playerCount && $pos < strlen($data); $i++) {
            $index = ord($data[$pos++]);
            
            // Oyuncu adını al
            $name = '';
            while ($pos < strlen($data) && $data[$pos] !== "\x00") {
                $name .= $data[$pos++];
            }
            $pos++; // null terminator
            
            // Skor (4 bytes)
            if ($pos + 4 <= strlen($data)) {
                $score = unpack('V', substr($data, $pos, 4))[1];
                $pos += 4;
            } else {
                $score = 0;
            }
            
            // Süre (4 bytes float)
            if ($pos + 4 <= strlen($data)) {
                $duration = unpack('f', substr($data, $pos, 4))[1];
                $pos += 4;
            } else {
                $duration = 0;
            }
            
            $players[] = [
                'index' => $index,
                'name' => $this->cleanString($name),
                'score' => $score,
                'duration' => round($duration, 2)
            ];
        }
        
        return $players;
    }
    
    /**
     * Socket bağlantısı kurar
     */
    private function connect() {
        $this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        
        if (!$this->socket) {
            throw new Exception("Socket oluşturulamadı: " . socket_strerror(socket_last_error()));
        }
        
        socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, [
            'sec' => $this->timeout,
            'usec' => 0
        ]);
        
        socket_set_option($this->socket, SOL_SOCKET, SO_SNDTIMEO, [
            'sec' => $this->timeout,
            'usec' => 0
        ]);
    }
    
    /**
     * Paket gönderir
     */
    private function sendPacket($packet) {
        $result = socket_sendto($this->socket, $packet, strlen($packet), 0, $this->ip, $this->port);
        
        if ($result === false) {
            throw new Exception("Paket gönderilemedi: " . socket_strerror(socket_last_error($this->socket)));
        }
    }
    
    /**
     * Paket alır
     */
    private function receivePacket() {
        $buffer = '';
        $from = '';
        $port = 0;
        
        $result = socket_recvfrom($this->socket, $buffer, 4096, 0, $from, $port);
        
        if ($result === false) {
            $error = socket_last_error($this->socket);
            if ($error === SOCKET_ETIMEDOUT) {
                throw new Exception("Sunucu yanıt vermiyor (timeout)");
            }
            throw new Exception("Paket alınamadı: " . socket_strerror($error));
        }
        
        return $buffer;
    }
    
    /**
     * Socket bağlantısını kapatır
     */
    private function disconnect() {
        if ($this->socket) {
            socket_close($this->socket);
            $this->socket = null;
        }
    }
    
    /**
     * Ping hesaplar
     */
    private function calculatePing() {
        $start = microtime(true);
        
        try {
            $this->connect();
            $packet = "\xFF\xFF\xFF\xFF\x54Source Engine Query\x00";
            $this->sendPacket($packet);
            $this->receivePacket();
            $this->disconnect();
            
            $end = microtime(true);
            return round(($end - $start) * 1000, 2);
            
        } catch (Exception $e) {
            return -1;
        }
    }
    
    /**
     * String temizleme
     */
    private function cleanString($str) {
        return trim(preg_replace('/[^\x20-\x7E]/', '', $str));
    }
}

// Ana işlem
try {
    // Parametreleri al
    $ip = $_GET['ip'] ?? '95.173.173.33';
    $port = (int)($_GET['port'] ?? 27015);
    
    // IP validasyonu
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        throw new Exception("Geçersiz IP adresi");
    }
    
    // Port validasyonu
    if ($port < 1 || $port > 65535) {
        throw new Exception("Geçersiz port numarası");
    }
    
    // Server query oluştur ve çalıştır
    $query = new CS16ServerQuery($ip, $port, 5);
    $result = $query->getServerInfo();
    
    // Sonucu döndür
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // Hata durumu
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'error_code' => 'INVALID_PARAMETERS'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?>
