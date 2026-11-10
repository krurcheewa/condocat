# คำสั่งทั้งหมดที่ใช้สร้างระบบ CondoCat

รวมคำสั่งทุกขั้นตอนตามลำดับที่ใช้จริง รันใน PowerShell บน Windows (XAMPP อยู่ที่ `C:\xampp`)
ให้รันจากโฟลเดอร์โปรเจกต์: `cd C:\xampp\htdocs\condocat`

---

## 1. ตรวจสอบเครื่องและ XAMPP

```powershell
# ดูไฟล์ในโฟลเดอร์โปรเจกต์, เช็กว่ามี mysql, เวอร์ชัน PHP และ Apache/MySQL ทำงานอยู่หรือไม่
Get-ChildItem C:\xampp\htdocs\condocat -Force
Test-Path C:\xampp\mysql\bin\mysql.exe
C:\xampp\php\php.exe -v
Get-Process mysqld,httpd -ErrorAction SilentlyContinue | Select-Object Name,Id
```

```powershell
# เช็กการเชื่อมต่อ MySQL และดูว่ามีฐานข้อมูล condocat หรือยัง
& C:\xampp\mysql\bin\mysql.exe -uroot -e "SELECT VERSION(); SHOW DATABASES LIKE 'condocat';"
& C:\xampp\mysql\bin\mysql.exe -uroot -e "USE condocat; SHOW TABLES;"
```

## 2. สร้างรหัสผ่านแบบเข้ารหัส (hash) สำหรับบัญชีตั้งต้น

```powershell
# ใส่รหัสผ่านที่ต้องการแทน 'รหัสผ่าน' แล้วนำค่า hash ที่ได้ไปใส่ในตาราง users
C:\xampp\php\php.exe -r "echo password_hash('รหัสผ่าน', PASSWORD_DEFAULT), PHP_EOL;"
```

## 3. สร้างฐานข้อมูลและนำเข้าข้อมูลตั้งต้น

```powershell
# import ทั้งระบบ (ลบตารางเดิมแล้วสร้างใหม่) จากไฟล์ database\condocat.sql
cmd /c "C:\xampp\mysql\bin\mysql.exe -uroot --default-character-set=utf8mb4 < database\condocat.sql"

# ตรวจว่าตารางถูกสร้างครบ
& C:\xampp\mysql\bin\mysql.exe -uroot condocat -e "SHOW TABLES; SELECT COUNT(*) FROM product_variants;"
```

## 4. ตรวจไวยากรณ์ PHP ทุกไฟล์

```powershell
# แสดงเฉพาะไฟล์ที่มีข้อผิดพลาด (ถ้าไม่มีอะไรแสดง = ผ่านทุกไฟล์)
Get-ChildItem C:\xampp\htdocs\condocat -Recurse -Filter *.php | ForEach-Object { C:\xampp\php\php.exe -l $_.FullName } | Where-Object { $_ -notmatch '^No syntax errors' }
```

## 5. ตรวจบัญชีผู้ดูแลระบบ

```powershell
& C:\xampp\mysql\bin\mysql.exe -uroot condocat -e "SELECT id, name, email, role FROM users WHERE role='admin';"
```

## 6. เพิ่มหมวดหมู่สินค้าเกี่ยวกับแมว 7 หมวด (รวมเป็น 10 หมวด)

รันได้ใน phpMyAdmin แท็บ SQL หรือบันทึกเป็นไฟล์ `.sql` แล้ว import (รันซ้ำได้ ไม่เพิ่มซ้ำ)

```sql
INSERT INTO categories (name, description)
SELECT * FROM (
  SELECT 'ที่ลับเล็บแมว' n, 'เสา แผ่น และกล่องลับเล็บ' d UNION ALL
  SELECT 'บ้านแมวและที่นอน', 'บ้านแมว เบาะนอน เปลแมว' UNION ALL
  SELECT 'ของเล่นแมว', 'ไม้ตกแมว บอล อุโมงค์ ของเล่นแคทนิป' UNION ALL
  SELECT 'ชามอาหารและน้ำพุ', 'ชามอาหาร ชามน้ำ น้ำพุแมว ที่ให้อาหารอัตโนมัติ' UNION ALL
  SELECT 'ห้องน้ำและทรายแมว', 'กระบะทราย ห้องน้ำแมว ทรายแมว ที่ตักทราย' UNION ALL
  SELECT 'กระเป๋าและกรงเดินทาง', 'กระเป๋าใส่แมว กรงเดินทาง รถเข็นแมว' UNION ALL
  SELECT 'อุปกรณ์ดูแลขน', 'แปรงขน หวี กรรไกรตัดเล็บ แชมพูแมว'
) t WHERE NOT EXISTS (SELECT 1 FROM categories c WHERE c.name = t.n);

SELECT id, name, description FROM categories ORDER BY id;
```

## 7. เพิ่มสินค้าตัวอย่างหมวดละ 2 รายการ พร้อมราคาและสต็อกตามขนาด

คำสั่ง SQL ทั้งหมดอยู่ในไฟล์ `database\sample_products.sql` (รันซ้ำได้ ไม่เพิ่มซ้ำ)

```powershell
cmd /c "C:\xampp\mysql\bin\mysql.exe -uroot --default-character-set=utf8mb4 condocat < database\sample_products.sql"

# ตรวจจำนวนสินค้าและขนาดในแต่ละหมวด
& C:\xampp\mysql\bin\mysql.exe -uroot condocat -e "SELECT c.id cat, COUNT(DISTINCT p.id) products, COUNT(v.id) variants FROM categories c LEFT JOIN products p ON p.category_id=c.id LEFT JOIN product_variants v ON v.product_id=p.id GROUP BY c.id;"
```

## 8. สร้างภาพสินค้า (SVG) และผูกกับสินค้า

```powershell
C:\xampp\php\php.exe database\seed_images.php
```

---

## สรุป: ติดตั้งใหม่ทั้งหมดในเครื่องอื่น

ไฟล์ `database\condocat.sql` มีข้อมูลครบแล้ว (10 หมวดหมู่ + สินค้า 19 รายการ) จึงใช้แค่ 2 คำสั่ง:

```powershell
cd C:\xampp\htdocs\condocat
cmd /c "C:\xampp\mysql\bin\mysql.exe -uroot --default-character-set=utf8mb4 < database\condocat.sql"
C:\xampp\php\php.exe database\seed_images.php
```

จากนั้นเปิด http://localhost/condocat/ (หน้าร้าน) และ http://localhost/condocat/admin/ (หลังร้าน)
