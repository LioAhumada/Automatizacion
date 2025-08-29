<?php
// --- Config ---
$TOKEN = getenv("TELEGRAM_TOKEN");            // <- en Render: Settings → Environment → TELEGRAM_TOKEN
$API   = "https://api.telegram.org/bot{$TOKEN}/";

// --- Helpers ---
function send($chatId, $msg) {
  global $API;
  $ch = curl_init($API . "sendMessage");
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => ["chat_id"=>$chatId, "text"=>$msg, "parse_mode"=>"HTML"]
  ]);
  curl_exec($ch);
  curl_close($ch);
}

// normaliza: minúsculas, sin tildes, recorta, singulariza simple
function norm($s) {
  $s = mb_strtolower(trim($s), 'UTF-8');
  $s = strtr($s, [
    'á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n',
    'ä'=>'a','ë'=>'e','ï'=>'i','ö'=>'o','ü'=>'u'
  ]);
  // singularización simple (pasteles->pastel, bebidas->bebida, jugos->jugo, tortas->torta, detergentes->detergente, etc.)
  if (preg_match('/(es|s)$/u', $s)) {
    if (preg_match('/(les|nes|res|tes)$/u', $s)) $s = mb_substr($s, 0, -2, 'UTF-8'); // -es
    else $s = mb_substr($s, 0, -1, 'UTF-8'); // -s
  }
  return $s;
}

// --- Catálogo: alias → [canon, pasillo]
$catalog = [
  // Pasillo 1
  'carne'      => ['Carne', 1],
  'queso'      => ['Queso', 1],
  'jamon'      => ['Jamón', 1],
  // Pasillo 2
  'leche'      => ['Leche', 2],
  'yogur'      => ['Yogurth', 2],  // aceptará yogurt/yogur/yogurth
  'yogurt'     => ['Yogurth', 2],
  'yogurth'    => ['Yogurth', 2],
  'cereal'     => ['Cereal', 2],
  // Pasillo 3
  'bebida'     => ['Bebidas', 3],
  'jugo'       => ['Jugos', 3],
  // Pasillo 4
  'pan'        => ['Pan', 4],
  'pastel'     => ['Pasteles', 4],
  'torta'      => ['Tortas', 4],
  // Pasillo 5
  'detergente' => ['Detergente', 5],
  'lavaloza'   => ['Lavaloza', 5],
];

// Texto de ayuda y lista de pasillos
$listaPasillos = "📍 <b>Mapa de pasillos</b>\n".
"• Pasillo 1: Carne, Queso, Jamón\n".
"• Pasillo 2: Leche, Yogurth, Cereal\n".
"• Pasillo 3: Bebidas, Jugos\n".
"• Pasillo 4: Pan, Pasteles, Tortas\n".
"• Pasillo 5: Detergente, Lavaloza";

$help = "🤖 Soy el bot del súper. Pregúntame por un producto y te digo el pasillo.\n".
"Ejemplos:\n".
"• ¿en qué pasillo está el arroz? (no listado → te aviso)\n".
"• ¿dónde encuentro el <b>detergente</b>?\n\n".
"Comandos:\n".
"/help — cómo usarme\n".
"/pasillos — ver el mapa de pasillos";

// --- Entrada desde Telegram ---
$update = json_decode(file_get_contents("php://input"), true);
if (!$update) { echo "OK"; http_response_code(200); exit; }

$msg    = $update["message"] ?? $update["edited_message"] ?? null;
$chatId = $msg["chat"]["id"] ?? null;
$text   = $msg["text"] ?? "";

// --- Lógica ---
if ($chatId) {
  $t = norm($text);

  if ($t === '/start' || $t === '/help') {
    send($chatId, $help);
  }
  elseif ($t === '/pasillos') {
    send($chatId, $listaPasillos);
  }
  else {
    // Buscar cualquiera de los alias dentro del texto
    $hit = null;
    foreach ($catalog as $alias => [$canon, $pasillo]) {
      if (strpos($t, $alias) !== false) { $hit = [$canon, $pasillo]; break; }
    }

    if ($hit) {
      [$canon, $p] = $hit;
      send($chatId, "🛒 ‘<b>{$canon}</b>’ está en el <b>Pasillo {$p}</b>.");
    } else {
      send($chatId, "No encontré ese producto en el mapa.\n\n$listaPasillos");
    }
  }
}

echo "OK";
