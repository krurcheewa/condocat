
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
