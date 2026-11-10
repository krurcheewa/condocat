<?php
// สร้างภาพสินค้า (SVG) ลงโฟลเดอร์ uploads/ และผูกกับสินค้าในฐานข้อมูล
// วิธีใช้: C:\xampp\php\php.exe database\seed_images.php
// อัปเดตเฉพาะสินค้าที่ยังไม่มีรูป หรือยังใช้รูปที่สคริปต์นี้สร้าง (img-*.svg)

$pdo = new PDO('mysql:host=localhost;dbname=condocat;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$dir = __DIR__ . '/../uploads/';

// แมวนั่ง ใช้ประกอบภาพ
function cat(float $x, float $y, float $s = 1, string $c = '#4a4a4a'): string {
    return "<g transform=\"translate($x $y) scale($s)\" fill=\"$c\">
      <ellipse cx=\"0\" cy=\"0\" rx=\"22\" ry=\"14\"/><circle cx=\"-18\" cy=\"-10\" r=\"12\"/>
      <path d=\"M-27 -18l3-12 7 9zM-15 -20l6-10 3 12z\"/>
      <path d=\"M22 0q18 -4 16 -20\" stroke=\"$c\" stroke-width=\"5\" fill=\"none\" stroke-linecap=\"round\"/>
      <circle cx=\"-22\" cy=\"-11\" r=\"1.6\" fill=\"#fff\"/><circle cx=\"-14\" cy=\"-11\" r=\"1.6\" fill=\"#fff\"/></g>";
}
// เส้นพันเชือกบนเสา
function rope(float $x, float $y1, float $y2, float $w, string $c = '#a87b4f'): string {
    $d = '';
    for ($y = $y1 + 8; $y < $y2; $y += 10) $d .= "M$x {$y}h$w";
    return "<path d=\"$d\" stroke=\"$c\" stroke-width=\"2\"/>";
}
function svg(string $bg, string $body): string {
    return "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 400 300\">
  <rect width=\"400\" height=\"300\" fill=\"$bg\"/>
  <circle cx=\"340\" cy=\"60\" r=\"70\" fill=\"#fff\" opacity=\".35\"/><circle cx=\"50\" cy=\"250\" r=\"50\" fill=\"#fff\" opacity=\".25\"/>
  <ellipse cx=\"200\" cy=\"266\" rx=\"150\" ry=\"12\" fill=\"#000\" opacity=\".08\"/>
  $body
</svg>\n";
}

$images = [
'คอนโดแมวไม้สน Cozy Tower' => svg('#fdf0e1',
  '<rect x="90" y="250" width="220" height="16" rx="4" fill="#b98a5e"/>
  <rect x="130" y="70" width="18" height="180" fill="#e2c49b"/>' . rope(130, 70, 250, 18) . '
  <rect x="252" y="130" width="18" height="120" fill="#e2c49b"/>' . rope(252, 130, 250, 18) . '
  <rect x="160" y="182" width="110" height="68" rx="8" fill="#c99a6b"/><circle cx="215" cy="216" r="20" fill="#6f4a2c"/>
  <rect x="104" y="58" width="100" height="14" rx="6" fill="#c99a6b"/><rect x="226" y="118" width="96" height="14" rx="6" fill="#c99a6b"/>
  <path d="M234 118q40 30 80 0" fill="#f3e3cf" stroke="#c99a6b" stroke-width="3"/>' . cat(160, 44, 1, '#d9893e')),

'คอนโดเชือกปอ Classic' => svg('#eef1f4',
  '<rect x="80" y="248" width="240" height="18" rx="4" fill="#8d96a3"/>
  <rect x="120" y="100" width="22" height="148" fill="#e4cfa6"/>' . rope(120, 100, 248, 22) . '
  <rect x="190" y="60" width="22" height="188" fill="#e4cfa6"/>' . rope(190, 60, 248, 22) . '
  <rect x="262" y="140" width="22" height="108" fill="#e4cfa6"/>' . rope(262, 140, 248, 22) . '
  <rect x="98" y="90" width="70" height="12" rx="5" fill="#8d96a3"/><rect x="168" y="50" width="70" height="12" rx="5" fill="#8d96a3"/>
  <rect x="240" y="130" width="70" height="12" rx="5" fill="#8d96a3"/>
  <line x1="300" y1="142" x2="300" y2="180" stroke="#555" stroke-width="2"/><circle cx="300" cy="186" r="9" fill="#e86a6a"/>' . cat(212, 36, 0.9, '#3d3d3d')),

'ชั้นปีนติดผนัง Wall Jump' => svg('#e9f3ee',
  '<rect x="60" y="20" width="280" height="246" fill="#f7faf8" stroke="#d7e5dc"/>
  <rect x="90" y="210" width="80" height="12" rx="4" fill="#c49a6c"/><rect x="180" y="150" width="80" height="12" rx="4" fill="#c49a6c"/>
  <rect x="250" y="90" width="70" height="12" rx="4" fill="#c49a6c"/>
  <path d="M100 222v10M160 222v10M190 162v10M250 162v10M260 102v10M310 102v10" stroke="#8a6a49" stroke-width="4"/>
  <path d="M130 200q40 -60 90 -60" stroke="#9cc3ad" stroke-width="3" stroke-dasharray="6 6" fill="none"/>' . cat(290, 76, 0.9, '#f0a04b')),

'คอนโดแมว Cactus' => svg('#fff4e6',
  '<path d="M140 266l10-46h100l10 46z" fill="#e07a3f"/><rect x="138" y="210" width="124" height="14" rx="4" fill="#c4612a"/>
  <rect x="180" y="60" width="40" height="150" rx="20" fill="#7dbb7a"/>' . rope(180, 70, 205, 40, '#5f9a5c') . '
  <path d="M180 140h-30a14 14 0 0 1-14-14v-30" stroke="#7dbb7a" stroke-width="26" fill="none" stroke-linecap="round"/>
  <path d="M220 120h30a14 14 0 0 0 14-14v-24" stroke="#7dbb7a" stroke-width="26" fill="none" stroke-linecap="round"/>
  <circle cx="200" cy="58" r="8" fill="#f58cae"/><circle cx="136" cy="84" r="6" fill="#f58cae"/>' . cat(272, 66, 0.75, '#888')),

'คอนโดแมว Castle XL' => svg('#f1ecf8',
  '<rect x="90" y="120" width="220" height="146" fill="#cbb6e6"/>
  <rect x="70" y="80" width="60" height="186" fill="#b59ad8"/><rect x="270" y="80" width="60" height="186" fill="#b59ad8"/>
  <path d="M66 80l34-50 34 50zM266 80l34-50 34 50z" fill="#8e6cc0"/>
  <path d="M100 30v-18l16 6-16 6M300 30v-18l16 6-16 6" stroke="#8e6cc0" stroke-width="2" fill="#f58cae"/>
  <path d="M90 120v-12h20v12h20v-12h20v12h20v-12h20v12h20v-12h20v12h20v-12h20v12h20v-12h20v12" fill="#cbb6e6"/>
  <path d="M175 266v-50a25 25 0 0 1 50 0v50z" fill="#6c4f99"/>
  <circle cx="100" cy="150" r="14" fill="#6c4f99"/><circle cx="300" cy="150" r="14" fill="#6c4f99"/>
  <circle cx="150" cy="170" r="12" fill="#6c4f99"/><circle cx="250" cy="170" r="12" fill="#6c4f99"/>' . rope(70, 200, 266, 60, '#9a80c0') . rope(270, 200, 266, 60, '#9a80c0') . cat(214, 104, 0.85, '#fff')),

'แผ่นลับเล็บกระดาษลูกฟูก Wave' => svg('#fbf2e4',
  '<path d="M60 250q35-80 70-40t70-30 70 30 70-40v80H60z" fill="#d9a873"/>
  <path d="M60 250q35-80 70-40t70-30 70 30 70-40" stroke="#b9854f" stroke-width="4" fill="none"/>
  <path d="M80 250v-34M100 250v-50M120 250v-46M140 250v-36M160 250v-48M180 250v-60M200 250v-62M220 250v-58M240 250v-48M260 250v-40M280 250v-44M300 250v-56M320 250v-60" stroke="#c6935f" stroke-width="3"/>
  <rect x="60" y="250" width="280" height="14" rx="3" fill="#a5733f"/>
  <path d="M182 150c4-10 14-10 14 0" stroke="#7ab36f" stroke-width="4" fill="none"/>' . cat(250, 150, 1, '#555')),

'เสาลับเล็บเชือกปอ Sisal Post' => svg('#f4efe8',
  '<rect x="120" y="236" width="160" height="30" rx="6" fill="#8c7b6b"/>
  <rect x="180" y="50" width="40" height="186" fill="#e7d1a7"/>' . rope(180, 50, 236, 40) . '
  <rect x="164" y="40" width="72" height="16" rx="6" fill="#8c7b6b"/>
  <line x1="230" y1="56" x2="262" y2="120" stroke="#666" stroke-width="2"/><circle cx="264" cy="128" r="11" fill="#5ba7e0"/>
  <path d="M258 122l12 12M270 122l-12 12" stroke="#fff" stroke-width="2"/>' . cat(130, 222, 0.9, '#d9893e')),

'เบาะนอนแมวโดนัท Fluffy' => svg('#fdeef2',
  '<ellipse cx="200" cy="200" rx="140" ry="60" fill="#e9b7c6"/>
  <ellipse cx="200" cy="196" rx="138" ry="56" fill="none" stroke="#fff" stroke-width="10" stroke-dasharray="2 10" opacity=".7" stroke-linecap="round"/>
  <ellipse cx="200" cy="192" rx="88" ry="30" fill="#d08da3"/>
  <ellipse cx="200" cy="186" rx="52" ry="22" fill="#666"/><circle cx="160" cy="180" r="18" fill="#666"/>
  <path d="M146 168l3-14 8 10zM160 164l7-12 4 14z" fill="#666"/>
  <path d="M248 190q10 18-40 10" stroke="#666" stroke-width="8" fill="none" stroke-linecap="round"/>
  <path d="M152 182q4 3 8 0M164 182q4 3 8 0" stroke="#fff" stroke-width="2" fill="none"/>
  <text x="240" y="140" font-family="sans-serif" font-size="22" fill="#d08da3">z z</text>'),

'บ้านแมวโดม Felt Cave' => svg('#eef3ea',
  '<path d="M70 262c0-150 260-150 260 0z" fill="#9aa88f"/>
  <path d="M90 262c0-120 220-120 220 0" fill="none" stroke="#8a987f" stroke-width="3" stroke-dasharray="5 7"/>
  <ellipse cx="200" cy="230" rx="62" ry="50" fill="#3a3f36"/>
  <circle cx="182" cy="226" r="7" fill="#f2d14b"/><circle cx="218" cy="226" r="7" fill="#f2d14b"/>
  <ellipse cx="182" cy="226" rx="2" ry="5" fill="#222"/><ellipse cx="218" cy="226" rx="2" ry="5" fill="#222"/>
  <path d="M200 238l-5 4h10z" fill="#e98fa4"/>'),

'ไม้ตกแมวขนนก Feather Teaser' => svg('#fff7e0',
  '<line x1="60" y1="260" x2="250" y2="60" stroke="#b07a4a" stroke-width="8" stroke-linecap="round"/>
  <path d="M250 60q60 20 40 100" stroke="#777" stroke-width="2" fill="none"/>
  <circle cx="290" cy="166" r="8" fill="#f2c94c" stroke="#c99a2e" stroke-width="2"/>
  <path d="M290 174q-30 40-10 80q8-40 10-80z" fill="#e86a6a"/><path d="M290 174q30 40 10 80q-8-40-10-80z" fill="#5ba7e0"/>
  <path d="M290 174q0 50 0 90" stroke="#fff" stroke-width="2"/>' . cat(140, 230, 0.9, '#4a4a4a')),

'อุโมงค์แมวพับได้ Crinkle Tunnel' => svg('#e8f2fb',
  '<path d="M60 160h280v80H60z" fill="#5ba7e0"/>
  <path d="M100 160v80M140 160v80M180 160v80M220 160v80M260 160v80M300 160v80" stroke="#3f88c2" stroke-width="3"/>
  <ellipse cx="60" cy="200" rx="22" ry="40" fill="#3f88c2"/><ellipse cx="60" cy="200" rx="14" ry="32" fill="#1f4f75"/>
  <ellipse cx="340" cy="200" rx="22" ry="40" fill="#7dbfee"/>
  <ellipse cx="200" cy="160" rx="30" ry="10" fill="#1f4f75"/>
  <line x1="200" y1="150" x2="200" y2="120" stroke="#777" stroke-width="2"/><circle cx="200" cy="114" r="9" fill="#f2c94c"/>
  <circle cx="54" cy="196" r="4" fill="#f2d14b"/><circle cx="66" cy="196" r="4" fill="#f2d14b"/>'),

'ชามอาหารเซรามิกขาตั้ง Tilted Bowl' => svg('#f6f1ea',
  '<rect x="80" y="180" width="240" height="20" rx="6" fill="#c9a274"/>
  <path d="M100 200l-8 62M300 200l8 62M150 200l-4 62M250 200l4 62" stroke="#a37c52" stroke-width="10" stroke-linecap="round"/>
  <g transform="rotate(-12 150 165)"><path d="M100 150h100l-14 36h-72z" fill="#fff" stroke="#ddd" stroke-width="2"/><ellipse cx="150" cy="150" rx="50" ry="10" fill="#d9a873"/>
  <circle cx="135" cy="148" r="4" fill="#a5733f"/><circle cx="150" cy="146" r="4" fill="#a5733f"/><circle cx="165" cy="149" r="4" fill="#a5733f"/></g>
  <g transform="rotate(-12 250 165)"><path d="M200 150h100l-14 36h-72z" fill="#fff" stroke="#ddd" stroke-width="2"/><ellipse cx="250" cy="150" rx="50" ry="10" fill="#8cc8ef"/></g>
  <path d="M130 130q4-6 8 0q4-6 8 0" stroke="#e07a3f" stroke-width="2" fill="none"/>'),

'น้ำพุแมว Fresh Flow' => svg('#e6f6f6',
  '<path d="M110 260l12-90h156l12 90z" fill="#fff" stroke="#cfe3e3" stroke-width="3"/>
  <ellipse cx="200" cy="170" rx="80" ry="16" fill="#8fd3df"/>
  <path d="M140 210h120" stroke="#8fd3df" stroke-width="20" opacity=".5"/>
  <rect x="188" y="110" width="24" height="60" rx="6" fill="#f2f8f8" stroke="#cfe3e3" stroke-width="2"/>
  <path d="M200 110q-10-40-50 40M200 110q10-40 50 40M200 110v-20" stroke="#5ec2d6" stroke-width="5" fill="none" stroke-linecap="round"/>
  <circle cx="200" cy="88" r="6" fill="#5ec2d6"/><circle cx="150" cy="160" r="4" fill="#5ec2d6"/><circle cx="250" cy="160" r="4" fill="#5ec2d6"/>
  <circle cx="290" cy="240" r="6" fill="#7dbb7a"/>'),

'ห้องน้ำแมวทรงโดม Clean Dome' => svg('#eef0f7',
  '<rect x="80" y="210" width="240" height="54" rx="12" fill="#7d8fc9"/>
  <path d="M90 214c0-150 220-150 220 0z" fill="#a9b6e0"/>
  <path d="M160 214v-40a40 40 0 0 1 80 0v40z" fill="#5e6fa8"/>
  <path d="M160 174a40 40 0 0 1 80 0" stroke="#e8ecf7" stroke-width="4" fill="none"/>
  <rect x="250" y="106" width="30" height="8" rx="3" fill="#7d8fc9"/><path d="M256 110v-8" stroke="#7d8fc9" stroke-width="3"/>
  <path d="M320 260l26-60" stroke="#f2a65a" stroke-width="6" stroke-linecap="round"/><rect x="334" y="170" width="28" height="36" rx="5" fill="#f2a65a" transform="rotate(22 348 188)"/>'),

'ทรายแมวเต้าหู้ Tofu Litter' => svg('#eef7e8',
  '<path d="M130 60h140l10 200H120z" fill="#8cc070"/>
  <path d="M130 60l10-14h120l10 14" fill="#73a65a"/>
  <rect x="146" y="120" width="108" height="70" rx="8" fill="#fff"/>
  <text x="200" y="152" text-anchor="middle" font-family="sans-serif" font-weight="bold" font-size="22" fill="#5b8f45">TOFU</text>
  <text x="200" y="176" text-anchor="middle" font-family="sans-serif" font-size="13" fill="#8cc070">CAT LITTER</text>
  ' . cat(204, 92, 0.6, '#fff') . '
  <g fill="#f3ead2" stroke="#d8cba6"><rect x="290" y="236" width="14" height="10" rx="2"/><rect x="306" y="244" width="14" height="10" rx="2"/><rect x="296" y="250" width="14" height="10" rx="2"/><rect x="86" y="244" width="14" height="10" rx="2"/><rect x="70" y="250" width="14" height="10" rx="2"/></g>'),

'กระเป๋าเป้ใส่แมวแคปซูล Space Pack' => svg('#f9eef7',
  '<path d="M150 70q50-30 100 0" stroke="#7b5aa6" stroke-width="10" fill="none" stroke-linecap="round"/>
  <rect x="120" y="70" width="160" height="190" rx="40" fill="#f2c94c"/>
  <circle cx="200" cy="160" r="60" fill="#cde9f6" stroke="#fff" stroke-width="8"/>
  <circle cx="200" cy="172" r="30" fill="#e07a3f"/><path d="M174 152l4-18 12 12zM226 152l-4-18-12 12z" fill="#e07a3f"/>
  <circle cx="189" cy="168" r="4" fill="#333"/><circle cx="211" cy="168" r="4" fill="#333"/><path d="M200 178l-4 4h8z" fill="#e98fa4"/>
  <path d="M160 128q16-10 30-8" stroke="#fff" stroke-width="5" fill="none" stroke-linecap="round" opacity=".8"/>
  <circle cx="150" cy="240" r="4" fill="#c99a2e"/><circle cx="170" cy="240" r="4" fill="#c99a2e"/><circle cx="230" cy="240" r="4" fill="#c99a2e"/><circle cx="250" cy="240" r="4" fill="#c99a2e"/>'),

'กรงเดินทางพลาสติก Travel Kennel' => svg('#eaf1f5',
  '<rect x="80" y="110" width="240" height="150" rx="18" fill="#6aa5c9"/>
  <rect x="80" y="176" width="240" height="10" fill="#4f88ab"/>
  <path d="M150 110v-20a14 14 0 0 1 14-14h72a14 14 0 0 1 14 14v20" stroke="#4f88ab" stroke-width="12" fill="none"/>
  <rect x="98" y="130" width="96" height="110" rx="8" fill="#2f4f63"/>
  <path d="M110 130v110M126 130v110M142 130v110M158 130v110M174 130v110M98 160h96M98 200h96" stroke="#d6dde2" stroke-width="3"/>
  <circle cx="134" cy="182" r="5" fill="#f2d14b"/><circle cx="158" cy="182" r="5" fill="#f2d14b"/>
  <path d="M220 140h80M220 156h80M220 210h80M220 226h80" stroke="#8cc0de" stroke-width="5" stroke-linecap="round"/>'),

'แปรงหวีขนแมว Self-Clean Brush' => svg('#f2eefb',
  '<g transform="rotate(-25 200 160)">
  <rect x="190" y="150" width="30" height="120" rx="14" fill="#8e6cc0"/>
  <rect x="120" y="60" width="170" height="100" rx="40" fill="#b59ad8"/>
  <rect x="134" y="72" width="142" height="76" rx="32" fill="#efe9f8"/>
  <g fill="#9aa0a8">' . implode('', array_map(fn($i) => '<circle cx="' . (150 + ($i % 9) * 14) . '" cy="' . (86 + intdiv($i, 9) * 16) . '" r="2.5"/>', range(0, 35))) . '</g>
  <circle cx="205" cy="200" r="9" fill="#f2c94c"/></g>
  <path d="M290 230q20-20 40 0q-20 20-40 0z" fill="#c9c9c9" opacity=".7"/>'),

'แชมพูแมวสูตรอ่อนโยน Gentle Shampoo' => svg('#fff6ec',
  '<rect x="170" y="40" width="40" height="30" rx="4" fill="#e07a3f"/><path d="M210 50h30v10h-30" fill="#e07a3f"/>
  <path d="M150 90q0-20 40-20h0q40 0 40 20v160q0 12-12 12h-56q-12 0-12-12z" fill="#ffd9b8"/>
  <rect x="160" y="130" width="60" height="80" rx="8" fill="#fff"/>
  ' . cat(196, 162, 0.55, '#e07a3f') . '
  <text x="190" y="196" text-anchor="middle" font-family="sans-serif" font-size="10" font-weight="bold" fill="#c4612a">GENTLE</text>
  <g fill="#fff" stroke="#9fd3e8" stroke-width="2"><circle cx="270" cy="200" r="20"/><circle cx="300" cy="160" r="12"/><circle cx="110" cy="190" r="16"/><circle cx="96" cy="150" r="9"/><circle cx="290" cy="240" r="9"/></g>'),
];

$find = $pdo->prepare('SELECT id, image FROM products WHERE name = ?');
$upd = $pdo->prepare('UPDATE products SET image = ? WHERE id = ?');
foreach ($images as $name => $content) {
    $find->execute([$name]);
    $p = $find->fetch(PDO::FETCH_ASSOC);
    if (!$p) { echo "ข้าม (ไม่พบสินค้า): $name\n"; continue; }
    if ($p['image'] && !str_starts_with($p['image'], 'img-')) { echo "ข้าม (มีรูปที่อัปโหลดเองแล้ว): $name\n"; continue; }
    $file = 'img-' . $p['id'] . '.svg';
    file_put_contents($dir . $file, $content);
    $upd->execute([$file, $p['id']]);
    echo "สร้างแล้ว: $file  $name\n";
}
