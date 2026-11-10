-- ฐานข้อมูลร้านค้าออนไลน์คอนโดแมว (condocat)
CREATE DATABASE IF NOT EXISTS condocat CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE condocat;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS order_items, orders, product_variants, products, sizes, categories, users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  phone VARCHAR(20) DEFAULT NULL,
  address TEXT DEFAULT NULL,
  role ENUM('customer','admin') NOT NULL DEFAULT 'customer',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  description VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB;

-- ขนาดคอนโด (ใช้ร่วมกันทุกสินค้า)
CREATE TABLE sizes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(10) NOT NULL UNIQUE,
  name VARCHAR(50) NOT NULL,
  detail VARCHAR(255) DEFAULT NULL,
  sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT DEFAULT NULL,
  name VARCHAR(150) NOT NULL,
  description TEXT,
  material VARCHAR(150) DEFAULT NULL,
  image VARCHAR(255) DEFAULT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- สินค้าแต่ละตัวมีได้หลายขนาด แต่ละขนาดมีราคา/สต็อก/ขนาดจริงของตัวเอง
CREATE TABLE product_variants (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  size_id INT NOT NULL,
  dimension VARCHAR(100) DEFAULT NULL,
  levels INT DEFAULT NULL,
  price DECIMAL(10,2) NOT NULL,
  stock INT NOT NULL DEFAULT 0,
  UNIQUE KEY uq_product_size (product_id, size_id),
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FOREIGN KEY (size_id) REFERENCES sizes(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_no VARCHAR(20) NOT NULL UNIQUE,
  user_id INT DEFAULT NULL,
  customer_name VARCHAR(100) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  address TEXT NOT NULL,
  note TEXT,
  payment_method ENUM('transfer','cod') NOT NULL DEFAULT 'transfer',
  slip VARCHAR(255) DEFAULT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  shipping DECIMAL(10,2) NOT NULL DEFAULT 0,
  total DECIMAL(10,2) NOT NULL,
  status ENUM('pending','paid','shipping','completed','cancelled') NOT NULL DEFAULT 'pending',
  tracking_no VARCHAR(50) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  variant_id INT DEFAULT NULL,
  product_name VARCHAR(150) NOT NULL,
  size_name VARCHAR(50) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  qty INT NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ข้อมูลตั้งต้น
-- บัญชีทดสอบ: admin@condocat.local / admin123 (ผู้ดูแล), user@condocat.local / user123 (ลูกค้า)
INSERT INTO users (name, email, password, phone, address, role) VALUES
('ผู้ดูแลระบบ', 'admin@condocat.local', '$2y$12$bsaEjWNMWCUADssekW246uZ8hReqyo50zPjWl2zfhm1LRrd45cPfC', '0800000000', NULL, 'admin'),
('ลูกค้าทดสอบ', 'user@condocat.local', '$2y$12$G5xoNYFl9jbCV7ywvSHebOWGOym8pzt86eKtR0Gyf52jfJeznGejm', '0811111111', '99/1 ถ.สุขุมวิท กรุงเทพฯ 10110', 'customer');

INSERT INTO categories (name, description) VALUES
('คอนโดไม้', 'คอนโดแมวโครงไม้แข็งแรง'),
('คอนโดเชือกปอ', 'เสาลับเล็บพันเชือกปอ'),
('คอนโดติดผนัง', 'ประหยัดพื้นที่ ติดตั้งบนผนัง'),
('ที่ลับเล็บแมว', 'เสา แผ่น และกล่องลับเล็บ'),
('บ้านแมวและที่นอน', 'บ้านแมว เบาะนอน เปลแมว'),
('ของเล่นแมว', 'ไม้ตกแมว บอล อุโมงค์ ของเล่นแคทนิป'),
('ชามอาหารและน้ำพุ', 'ชามอาหาร ชามน้ำ น้ำพุแมว ที่ให้อาหารอัตโนมัติ'),
('ห้องน้ำและทรายแมว', 'กระบะทราย ห้องน้ำแมว ทรายแมว ที่ตักทราย'),
('กระเป๋าและกรงเดินทาง', 'กระเป๋าใส่แมว กรงเดินทาง รถเข็นแมว'),
('อุปกรณ์ดูแลขน', 'แปรงขน หวี กรรไกรตัดเล็บ แชมพูแมว');

INSERT INTO sizes (code, name, detail, sort_order) VALUES
('S',  'เล็ก (S)',      'เหมาะกับลูกแมว / แมว 1 ตัว', 1),
('M',  'กลาง (M)',      'เหมาะกับแมว 1-2 ตัว', 2),
('L',  'ใหญ่ (L)',      'เหมาะกับแมว 2-3 ตัว', 3),
('XL', 'ใหญ่พิเศษ (XL)', 'เหมาะกับบ้านแมวหลายตัว', 4);

INSERT INTO products (category_id, name, description, material) VALUES
(1, 'คอนโดแมวไม้สน Cozy Tower', 'คอนโดไม้สนธรรมชาติ มีบ้านซ่อนตัว เปลนอน และเสาลับเล็บ ทนทานใช้งานได้นาน', 'ไม้สน + เชือกปอ + ผ้าขนนุ่ม'),
(2, 'คอนโดเชือกปอ Classic', 'คอนโดยอดนิยม เสาพันเชือกปอแน่นหนา ให้แมวลับเล็บได้เต็มที่ พร้อมของเล่นห้อย', 'ไม้อัด MDF + เชือกปอ + ผ้าพรม'),
(3, 'ชั้นปีนติดผนัง Wall Jump', 'ชั้นปีนติดผนังสไตล์มินิมอล ประหยัดพื้นที่ เหมาะกับคอนโดและห้องเล็ก', 'ไม้ยางพารา + พรมกันลื่น'),
(1, 'คอนโดแมว Cactus', 'คอนโดทรงกระบองเพชรน่ารัก ตกแต่งห้องได้สวย มีเสาลับเล็บรอบตัว', 'ไม้อัด + เชือกปอย้อมสี'),
(2, 'คอนโดแมว Castle XL', 'ปราสาทแมวขนาดใหญ่ หลายชั้น หลายห้อง สำหรับทาสแมวหลายตัว', 'ไม้อัด MDF + เชือกปอ + ผ้าขนนุ่ม');

INSERT INTO product_variants (product_id, size_id, dimension, levels, price, stock) VALUES
(1, 1, '40x40x60 ซม.', 2, 890, 15),
(1, 2, '50x50x100 ซม.', 3, 1490, 10),
(1, 3, '60x60x140 ซม.', 4, 2290, 6),
(2, 1, '35x35x50 ซม.', 2, 590, 20),
(2, 2, '45x45x90 ซม.', 3, 1090, 12),
(2, 3, '55x55x130 ซม.', 4, 1690, 8),
(2, 4, '70x60x180 ซม.', 6, 2890, 4),
(3, 1, '3 ชิ้น', 3, 790, 10),
(3, 2, '5 ชิ้น', 5, 1290, 7),
(3, 3, '8 ชิ้น', 8, 1990, 5),
(4, 2, '45x45x95 ซม.', 3, 1390, 9),
(4, 3, '55x55x120 ซม.', 4, 1890, 5),
(5, 3, '80x60x160 ซม.', 5, 3490, 3),
(5, 4, '100x70x200 ซม.', 7, 4990, 2);

-- สินค้าตัวอย่างหมวดละ 2 รายการ (หมวดที่ 4-10)
-- รันซ้ำได้: ข้ามสินค้าที่มีชื่อซ้ำ และข้ามขนาดที่มีอยู่แล้ว
INSERT INTO products (category_id, name, description, material)
SELECT c.id, t.name, t.description, t.material FROM (
  SELECT 'ที่ลับเล็บแมว' AS cat, 'แผ่นลับเล็บกระดาษลูกฟูก Wave' AS name, 'แผ่นลับเล็บทรงคลื่น ใช้เป็นที่นอนเล่นได้ด้วย ช่วยถนอมเฟอร์นิเจอร์ในบ้าน' AS description, 'กระดาษลูกฟูกอัดแน่น + แคทนิป' AS material UNION ALL
  SELECT 'ที่ลับเล็บแมว', 'เสาลับเล็บเชือกปอ Sisal Post', 'เสาลับเล็บตั้งพื้น ฐานกว้างไม่ล้มง่าย มีลูกบอลห้อยด้านบน', 'เชือกปอธรรมชาติ + ฐานไม้อัดหุ้มพรม' UNION ALL
  SELECT 'บ้านแมวและที่นอน', 'เบาะนอนแมวโดนัท Fluffy', 'เบาะทรงโดนัทขนนุ่มฟู ขอบสูงรองรับคอ ช่วยให้แมวรู้สึกปลอดภัย ซักเครื่องได้', 'ผ้าขนเฟอร์ + ใยสังเคราะห์' UNION ALL
  SELECT 'บ้านแมวและที่นอน', 'บ้านแมวโดม Felt Cave', 'บ้านแมวทรงถ้ำจากผ้าสักหลาด อบอุ่นในหน้าหนาว ระบายอากาศดีในหน้าร้อน', 'ผ้าสักหลาดขนแกะ' UNION ALL
  SELECT 'ของเล่นแมว', 'ไม้ตกแมวขนนก Feather Teaser', 'ไม้ตกแมวก้านยืดหยุ่น ปลายขนนกพร้อมกระดิ่ง เปลี่ยนหัวได้', 'ก้านไฟเบอร์ + ขนนก' UNION ALL
  SELECT 'ของเล่นแมว', 'อุโมงค์แมวพับได้ Crinkle Tunnel', 'อุโมงค์ผ้าเสียงกรอบแกรบ พับเก็บได้ มีลูกบอลห้อยตรงช่องกลาง', 'ผ้าโพลีเอสเตอร์ + โครงลวดสปริง' UNION ALL
  SELECT 'ชามอาหารและน้ำพุ', 'ชามอาหารเซรามิกขาตั้ง Tilted Bowl', 'ชามเอียง 15 องศา ช่วยให้กินง่าย ลดการก้มคอ ล้างทำความสะอาดง่าย', 'เซรามิก + ขาตั้งไม้บีช' UNION ALL
  SELECT 'ชามอาหารและน้ำพุ', 'น้ำพุแมว Fresh Flow', 'น้ำพุหมุนเวียนกระตุ้นให้แมวดื่มน้ำมากขึ้น ปั๊มเงียบ มีไส้กรองคาร์บอน', 'พลาสติก ABS ปลอด BPA' UNION ALL
  SELECT 'ห้องน้ำและทรายแมว', 'ห้องน้ำแมวทรงโดม Clean Dome', 'ห้องน้ำแบบมีฝาครอบ ลดกลิ่นและทรายกระเด็น มีแผ่นกรองกลิ่นและที่ตักทราย', 'พลาสติก PP' UNION ALL
  SELECT 'ห้องน้ำและทรายแมว', 'ทรายแมวเต้าหู้ Tofu Litter', 'ทรายเต้าหู้จับตัวเป็นก้อนเร็ว ฝุ่นน้อย ทิ้งลงชักโครกได้ กลิ่นชาเขียว', 'กากถั่วเหลืองธรรมชาติ' UNION ALL
  SELECT 'กระเป๋าและกรงเดินทาง', 'กระเป๋าเป้ใส่แมวแคปซูล Space Pack', 'เป้แคปซูลมีหน้าต่างใส ระบายอากาศรอบด้าน สายสะพายบุนุ่ม', 'พลาสติก PC + ผ้าออกซ์ฟอร์ด' UNION ALL
  SELECT 'กระเป๋าและกรงเดินทาง', 'กรงเดินทางพลาสติก Travel Kennel', 'กรงหิ้วแข็งแรง ประตูเหล็กล็อกแน่น เหมาะพาไปหาหมอหรือเดินทางไกล', 'พลาสติก PP + ประตูเหล็กชุบ' UNION ALL
  SELECT 'อุปกรณ์ดูแลขน', 'แปรงหวีขนแมว Self-Clean Brush', 'แปรงสลิกเกอร์กดปุ่มดันขนออกได้ทันที ช่วยลดขนร่วงและก้อนขน', 'ซี่สแตนเลส + ด้ามพลาสติกกันลื่น' UNION ALL
  SELECT 'อุปกรณ์ดูแลขน', 'แชมพูแมวสูตรอ่อนโยน Gentle Shampoo', 'แชมพูสูตรอ่อนโยน pH สมดุลสำหรับแมว ขนนุ่มหอม ไม่ระคายเคือง', 'สารสกัดข้าวโอ๊ต + ว่านหางจระเข้'
) t JOIN categories c ON c.name = t.cat
WHERE NOT EXISTS (SELECT 1 FROM products p WHERE p.name = t.name);

INSERT IGNORE INTO product_variants (product_id, size_id, dimension, levels, price, stock)
SELECT p.id, s.id, t.dim, NULL, t.price, t.stock FROM (
  SELECT 'แผ่นลับเล็บกระดาษลูกฟูก Wave' AS pname, 'S' AS code, '45x25x8 ซม.' AS dim, 159 AS price, 30 AS stock UNION ALL
  SELECT 'แผ่นลับเล็บกระดาษลูกฟูก Wave', 'M', '60x30x10 ซม.', 249, 25 UNION ALL
  SELECT 'แผ่นลับเล็บกระดาษลูกฟูก Wave', 'L', '80x35x12 ซม.', 359, 15 UNION ALL
  SELECT 'เสาลับเล็บเชือกปอ Sisal Post', 'S', 'สูง 40 ซม.', 290, 20 UNION ALL
  SELECT 'เสาลับเล็บเชือกปอ Sisal Post', 'M', 'สูง 60 ซม.', 450, 15 UNION ALL
  SELECT 'เสาลับเล็บเชือกปอ Sisal Post', 'L', 'สูง 80 ซม.', 650, 8 UNION ALL
  SELECT 'เบาะนอนแมวโดนัท Fluffy', 'S', 'เส้นผ่านศูนย์กลาง 40 ซม.', 350, 20 UNION ALL
  SELECT 'เบาะนอนแมวโดนัท Fluffy', 'M', 'เส้นผ่านศูนย์กลาง 50 ซม.', 450, 15 UNION ALL
  SELECT 'เบาะนอนแมวโดนัท Fluffy', 'L', 'เส้นผ่านศูนย์กลาง 60 ซม.', 590, 10 UNION ALL
  SELECT 'เบาะนอนแมวโดนัท Fluffy', 'XL', 'เส้นผ่านศูนย์กลาง 80 ซม.', 790, 5 UNION ALL
  SELECT 'บ้านแมวโดม Felt Cave', 'S', '35x35x30 ซม.', 490, 12 UNION ALL
  SELECT 'บ้านแมวโดม Felt Cave', 'M', '45x45x38 ซม.', 690, 8 UNION ALL
  SELECT 'บ้านแมวโดม Felt Cave', 'L', '55x55x45 ซม.', 890, 5 UNION ALL
  SELECT 'ไม้ตกแมวขนนก Feather Teaser', 'S', 'ก้านยาว 40 ซม.', 79, 50 UNION ALL
  SELECT 'ไม้ตกแมวขนนก Feather Teaser', 'M', 'ก้านยาว 70 ซม.', 119, 40 UNION ALL
  SELECT 'ไม้ตกแมวขนนก Feather Teaser', 'L', 'ก้านยาว 100 ซม.', 159, 25 UNION ALL
  SELECT 'อุโมงค์แมวพับได้ Crinkle Tunnel', 'S', 'ทางตรง ยาว 90 ซม.', 259, 15 UNION ALL
  SELECT 'อุโมงค์แมวพับได้ Crinkle Tunnel', 'M', '3 ทางแยก', 390, 10 UNION ALL
  SELECT 'อุโมงค์แมวพับได้ Crinkle Tunnel', 'L', '4 ทางแยก', 520, 6 UNION ALL
  SELECT 'ชามอาหารเซรามิกขาตั้ง Tilted Bowl', 'S', 'ชามเดี่ยว 150 มล.', 190, 30 UNION ALL
  SELECT 'ชามอาหารเซรามิกขาตั้ง Tilted Bowl', 'M', 'ชามเดี่ยว 300 มล.', 250, 20 UNION ALL
  SELECT 'ชามอาหารเซรามิกขาตั้ง Tilted Bowl', 'L', 'ชามคู่ 2x300 มล.', 390, 12 UNION ALL
  SELECT 'น้ำพุแมว Fresh Flow', 'S', 'ความจุ 1.5 ลิตร', 690, 10 UNION ALL
  SELECT 'น้ำพุแมว Fresh Flow', 'M', 'ความจุ 2.5 ลิตร', 890, 8 UNION ALL
  SELECT 'น้ำพุแมว Fresh Flow', 'L', 'ความจุ 4 ลิตร', 1190, 5 UNION ALL
  SELECT 'ห้องน้ำแมวทรงโดม Clean Dome', 'M', '45x38x40 ซม.', 590, 10 UNION ALL
  SELECT 'ห้องน้ำแมวทรงโดม Clean Dome', 'L', '55x42x45 ซม.', 790, 8 UNION ALL
  SELECT 'ห้องน้ำแมวทรงโดม Clean Dome', 'XL', '65x50x50 ซม.', 990, 4 UNION ALL
  SELECT 'ทรายแมวเต้าหู้ Tofu Litter', 'S', 'ถุง 6 ลิตร', 129, 60 UNION ALL
  SELECT 'ทรายแมวเต้าหู้ Tofu Litter', 'M', 'ถุง 10 ลิตร', 199, 40 UNION ALL
  SELECT 'ทรายแมวเต้าหู้ Tofu Litter', 'L', 'ถุง 18 ลิตร', 339, 20 UNION ALL
  SELECT 'กระเป๋าเป้ใส่แมวแคปซูล Space Pack', 'M', '32x28x42 ซม. (แมวไม่เกิน 6 กก.)', 790, 8 UNION ALL
  SELECT 'กระเป๋าเป้ใส่แมวแคปซูล Space Pack', 'L', '36x30x46 ซม. (แมวไม่เกิน 9 กก.)', 990, 5 UNION ALL
  SELECT 'กรงเดินทางพลาสติก Travel Kennel', 'S', '48x32x30 ซม.', 590, 10 UNION ALL
  SELECT 'กรงเดินทางพลาสติก Travel Kennel', 'M', '58x38x36 ซม.', 790, 7 UNION ALL
  SELECT 'กรงเดินทางพลาสติก Travel Kennel', 'L', '68x48x48 ซม.', 1090, 4 UNION ALL
  SELECT 'แปรงหวีขนแมว Self-Clean Brush', 'S', 'หัวแปรง 8 ซม.', 159, 30 UNION ALL
  SELECT 'แปรงหวีขนแมว Self-Clean Brush', 'L', 'หัวแปรง 12 ซม.', 229, 20 UNION ALL
  SELECT 'แชมพูแมวสูตรอ่อนโยน Gentle Shampoo', 'S', 'ขวด 250 มล.', 189, 25 UNION ALL
  SELECT 'แชมพูแมวสูตรอ่อนโยน Gentle Shampoo', 'M', 'ขวด 500 มล.', 299, 15 UNION ALL
  SELECT 'แชมพูแมวสูตรอ่อนโยน Gentle Shampoo', 'L', 'ขวด 1,000 มล.', 499, 8
) t JOIN products p ON p.name = t.pname JOIN sizes s ON s.code = t.code;
