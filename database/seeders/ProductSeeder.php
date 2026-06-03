    <?php

    namespace Database\Seeders;

    use App\Models\Category;
    use App\Models\Product;
    use Illuminate\Database\Seeder;

    class ProductSeeder extends Seeder
    {
        /**
         * Run the database seeds.
         */
        public function run(): void
        {
            // Data produk berdasarkan file Excel
            $products = [
                ['name' => 'Aqua 600ml', 'purchase_price' => 2500, 'code' => 'PRD-001', 'selling_price' => 3500, 'category' => 'Minuman', 'stock' => 40, 'unit' => 'botol'],
                ['name' => 'Aqua 1500ml', 'purchase_price' => 4500, 'code' => 'PRD-002', 'selling_price' => 6000, 'category' => 'Minuman', 'stock' => 106, 'unit' => 'botol'],
                ['name' => 'Aqua Galon 19L', 'purchase_price' => 18000, 'code' => 'PRD-003', 'selling_price' => 22000, 'category' => 'Minuman', 'stock' => 81, 'unit' => 'botol'],
                ['name' => 'Vit 600ml', 'purchase_price' => 2000, 'code' => 'PRD-004', 'selling_price' => 3000, 'category' => 'Minuman', 'stock' => 47, 'unit' => 'botol'],
                ['name' => 'Vit 1500ml', 'purchase_price' => 4000, 'code' => 'PRD-005', 'selling_price' => 5500, 'category' => 'Minuman', 'stock' => 33, 'unit' => 'botol'],
                ['name' => 'Cleo 550ml', 'purchase_price' => 2800, 'code' => 'PRD-006', 'selling_price' => 4000, 'category' => 'Minuman', 'stock' => 69, 'unit' => 'botol'],
                ['name' => 'Ades 600ml', 'purchase_price' => 2200, 'code' => 'PRD-007', 'selling_price' => 3000, 'category' => 'Minuman', 'stock' => 48, 'unit' => 'botol'],
                ['name' => 'Nestle Pure Life 600ml', 'purchase_price' => 2500, 'code' => 'PRD-008', 'selling_price' => 3500, 'category' => 'Minuman', 'stock' => 40, 'unit' => 'botol'],
                ['name' => 'Mizone Apel 500ml', 'purchase_price' => 5000, 'code' => 'PRD-009', 'selling_price' => 7000, 'category' => 'Minuman', 'stock' => 87, 'unit' => 'botol'],
                ['name' => 'Mizone Lychee 500ml', 'purchase_price' => 5000, 'code' => 'PRD-010', 'selling_price' => 7000, 'category' => 'Minuman', 'stock' => 86, 'unit' => 'botol'],
                ['name' => 'Mizone Passion Fruit 500ml', 'purchase_price' => 5000, 'code' => 'PRD-011', 'selling_price' => 7000, 'category' => 'Minuman', 'stock' => 72, 'unit' => 'botol'],
                ['name' => 'Pocari Sweat 500ml', 'purchase_price' => 7000, 'code' => 'PRD-012', 'selling_price' => 10000, 'category' => 'Minuman', 'stock' => 129, 'unit' => 'botol'],
                ['name' => 'Pocari Sweat 350ml', 'purchase_price' => 5000, 'code' => 'PRD-013', 'selling_price' => 7500, 'category' => 'Minuman', 'stock' => 74, 'unit' => 'botol'],
                ['name' => 'Kratingdaeng 150ml', 'purchase_price' => 6000, 'code' => 'PRD-014', 'selling_price' => 9000, 'category' => 'Minuman', 'stock' => 128, 'unit' => 'kaleng'],
                ['name' => 'Extra Joss Kaleng 250ml', 'purchase_price' => 6500, 'code' => 'PRD-015', 'selling_price' => 9000, 'category' => 'Minuman', 'stock' => 141, 'unit' => 'kaleng'],
                ['name' => 'M-150 150ml', 'purchase_price' => 5500, 'code' => 'PRD-016', 'selling_price' => 8000, 'category' => 'Minuman', 'stock' => 33, 'unit' => 'botol'],
                ['name' => 'Teh Botol Sosro 450ml', 'purchase_price' => 3500, 'code' => 'PRD-017', 'selling_price' => 5000, 'category' => 'Minuman', 'stock' => 122, 'unit' => 'botol'],
                ['name' => 'Teh Botol Sosro 250ml', 'purchase_price' => 2500, 'code' => 'PRD-018', 'selling_price' => 3500, 'category' => 'Minuman', 'stock' => 142, 'unit' => 'botol'],
                ['name' => 'Frestea Green 500ml', 'purchase_price' => 5000, 'code' => 'PRD-019', 'selling_price' => 7000, 'category' => 'Minuman', 'stock' => 114, 'unit' => 'botol'],
                ['name' => 'Frestea Apple 500ml', 'purchase_price' => 5000, 'code' => 'PRD-020', 'selling_price' => 7000, 'category' => 'Minuman', 'stock' => 121, 'unit' => 'botol'],
                ['name' => 'Fruit Tea Strawberry 350ml', 'purchase_price' => 4000, 'code' => 'PRD-021', 'selling_price' => 6000, 'category' => 'Minuman', 'stock' => 108, 'unit' => 'botol'],
                ['name' => 'Fruit Tea Apple 350ml', 'purchase_price' => 4000, 'code' => 'PRD-022', 'selling_price' => 6000, 'category' => 'Minuman', 'stock' => 108, 'unit' => 'botol'],
                ['name' => 'Tebs 500ml', 'purchase_price' => 5000, 'code' => 'PRD-023', 'selling_price' => 7000, 'category' => 'Minuman', 'stock' => 50, 'unit' => 'botol'],
                ['name' => 'Nu Green Tea 330ml', 'purchase_price' => 4500, 'code' => 'PRD-024', 'selling_price' => 6500, 'category' => 'Minuman', 'stock' => 64, 'unit' => 'botol'],
                ['name' => 'Pokka Green Tea 350ml', 'purchase_price' => 5500, 'code' => 'PRD-025', 'selling_price' => 7500, 'category' => 'Minuman', 'stock' => 96, 'unit' => 'botol'],
                ['name' => 'Coca-Cola 390ml', 'purchase_price' => 4500, 'code' => 'PRD-026', 'selling_price' => 6500, 'category' => 'Minuman', 'stock' => 23, 'unit' => 'botol'],
                ['name' => 'Sprite 390ml', 'purchase_price' => 4500, 'code' => 'PRD-027', 'selling_price' => 6500, 'category' => 'Minuman', 'stock' => 30, 'unit' => 'botol'],
                ['name' => 'Fanta Strawberry 390ml', 'purchase_price' => 4500, 'code' => 'PRD-028', 'selling_price' => 6500, 'category' => 'Minuman', 'stock' => 48, 'unit' => 'botol'],
                ['name' => 'Fanta Orange 390ml', 'purchase_price' => 4500, 'code' => 'PRD-029', 'selling_price' => 6500, 'category' => 'Minuman', 'stock' => 10, 'unit' => 'botol'],
                ['name' => 'Pulpy Orange 350ml', 'purchase_price' => 5000, 'code' => 'PRD-030', 'selling_price' => 7000, 'category' => 'Minuman', 'stock' => 51, 'unit' => 'botol'],
                ['name' => 'Minute Maid Pulpy 300ml', 'purchase_price' => 5000, 'code' => 'PRD-031', 'selling_price' => 7000, 'category' => 'Minuman', 'stock' => 120, 'unit' => 'botol'],
                ['name' => 'Lemon Water 350ml', 'purchase_price' => 4000, 'code' => 'PRD-032', 'selling_price' => 6000, 'category' => 'Minuman', 'stock' => 73, 'unit' => 'botol'],
                ['name' => 'Orange Water 350ml', 'purchase_price' => 4500, 'code' => 'PRD-033', 'selling_price' => 6000, 'category' => 'Minuman', 'stock' => 124, 'unit' => 'botol'],
                ['name' => 'Coco Bite Coconut Water 360ml', 'purchase_price' => 5000, 'code' => 'PRD-034', 'selling_price' => 7000, 'category' => 'Minuman', 'stock' => 133, 'unit' => 'botol'],
                ['name' => 'Ale-Ale Jeruk 200ml', 'purchase_price' => 2000, 'code' => 'PRD-035', 'selling_price' => 3000, 'category' => 'Minuman', 'stock' => 149, 'unit' => 'pcs'],
                ['name' => 'Ale-Ale Leci 200ml', 'purchase_price' => 2000, 'code' => 'PRD-036', 'selling_price' => 3000, 'category' => 'Minuman', 'stock' => 59, 'unit' => 'pcs'],
                ['name' => 'Okky Jelly Drink Anggur', 'purchase_price' => 2500, 'code' => 'PRD-037', 'selling_price' => 4000, 'category' => 'Minuman', 'stock' => 82, 'unit' => 'pcs'],
                ['name' => 'Okky Jelly Drink Leci', 'purchase_price' => 2500, 'code' => 'PRD-038', 'selling_price' => 4000, 'category' => 'Minuman', 'stock' => 94, 'unit' => 'pcs'],
                ['name' => 'Cap Kaki Tiga Larutan Penyegar 330ml', 'purchase_price' => 8000, 'code' => 'PRD-039', 'selling_price' => 11000, 'category' => 'Minuman', 'stock' => 81, 'unit' => 'kaleng'],
                ['name' => 'Milo Kaleng 240ml', 'purchase_price' => 8000, 'code' => 'PRD-040', 'selling_price' => 12000, 'category' => 'Minuman', 'stock' => 108, 'unit' => 'kaleng'],
                ['name' => 'Nescafe Ready Drink 240ml', 'purchase_price' => 7500, 'code' => 'PRD-041', 'selling_price' => 11000, 'category' => 'Kopi & Teh', 'stock' => 55, 'unit' => 'kaleng'],
                ['name' => 'Good Day Vanilla Latte Kaleng 240ml', 'purchase_price' => 7000, 'code' => 'PRD-042', 'selling_price' => 10000, 'category' => 'Kopi & Teh', 'stock' => 98, 'unit' => 'kaleng'],
                ['name' => 'Green Sands 330ml', 'purchase_price' => 6000, 'code' => 'PRD-043', 'selling_price' => 9000, 'category' => 'Minuman', 'stock' => 130, 'unit' => 'kaleng'],
                ['name' => 'Teh Kotak Sosro 200ml', 'purchase_price' => 3000, 'code' => 'PRD-044', 'selling_price' => 4500, 'category' => 'Minuman', 'stock' => 25, 'unit' => 'pcs'],
                ['name' => 'Ultra Milk Full Cream 200ml', 'purchase_price' => 4500, 'code' => 'PRD-045', 'selling_price' => 6000, 'category' => 'Produk Susu', 'stock' => 149, 'unit' => 'pcs'],
                ['name' => 'Ultra Milk Coklat 200ml', 'purchase_price' => 4500, 'code' => 'PRD-046', 'selling_price' => 6000, 'category' => 'Produk Susu', 'stock' => 116, 'unit' => 'pcs'],
                ['name' => 'Ultra Milk Strawberry 200ml', 'purchase_price' => 4500, 'code' => 'PRD-047', 'selling_price' => 6000, 'category' => 'Produk Susu', 'stock' => 53, 'unit' => 'pcs'],
                ['name' => 'Indomilk Kotak Coklat 200ml', 'purchase_price' => 4000, 'code' => 'PRD-048', 'selling_price' => 5500, 'category' => 'Produk Susu', 'stock' => 21, 'unit' => 'pcs'],
                ['name' => 'Indomilk Kotak Putih 200ml', 'purchase_price' => 4000, 'code' => 'PRD-049', 'selling_price' => 5500, 'category' => 'Produk Susu', 'stock' => 136, 'unit' => 'pcs'],
                ['name' => 'Fresh Milk Greenfields 200ml', 'purchase_price' => 5000, 'code' => 'PRD-050', 'selling_price' => 7000, 'category' => 'Produk Susu', 'stock' => 99, 'unit' => 'pcs'],
                ['name' => 'Milo UHT 200ml', 'purchase_price' => 4500, 'code' => 'PRD-051', 'selling_price' => 6500, 'category' => 'Produk Susu', 'stock' => 99, 'unit' => 'pcs'],
                ['name' => 'Yakult 65ml', 'purchase_price' => 3500, 'code' => 'PRD-052', 'selling_price' => 5000, 'category' => 'Produk Susu', 'stock' => 57, 'unit' => 'pcs'],
                ['name' => 'Cimory Yogurt Drink Strawberry 250ml', 'purchase_price' => 6000, 'code' => 'PRD-053', 'selling_price' => 9000, 'category' => 'Produk Susu', 'stock' => 62, 'unit' => 'botol'],
                ['name' => 'Nutriboost Orange 300ml', 'purchase_price' => 5500, 'code' => 'PRD-054', 'selling_price' => 8000, 'category' => 'Produk Susu', 'stock' => 100, 'unit' => 'botol'],
                ['name' => 'Marimas Sachet Jeruk', 'purchase_price' => 500, 'code' => 'PRD-055', 'selling_price' => 1000, 'category' => 'Minuman', 'stock' => 128, 'unit' => 'sachet'],
                ['name' => 'Chitato Original 68gr', 'purchase_price' => 8000, 'code' => 'PRD-056', 'selling_price' => 11000, 'category' => 'Snack', 'stock' => 145, 'unit' => 'pcs'],
                ['name' => 'Chitato Sapi Panggang 68gr', 'purchase_price' => 8000, 'code' => 'PRD-057', 'selling_price' => 11000, 'category' => 'Snack', 'stock' => 37, 'unit' => 'pcs'],
                ['name' => 'Chitato BBQ 68gr', 'purchase_price' => 8000, 'code' => 'PRD-058', 'selling_price' => 11000, 'category' => 'Snack', 'stock' => 100, 'unit' => 'pcs'],
                ['name' => 'Piattos Keju 68gr', 'purchase_price' => 7000, 'code' => 'PRD-059', 'selling_price' => 10000, 'category' => 'Snack', 'stock' => 57, 'unit' => 'pcs'],
                ['name' => 'Piattos Balado 68gr', 'purchase_price' => 7000, 'code' => 'PRD-060', 'selling_price' => 10000, 'category' => 'Snack', 'stock' => 114, 'unit' => 'pcs'],
                ['name' => 'Piattos Big & Crispy 68gr', 'purchase_price' => 7000, 'code' => 'PRD-061', 'selling_price' => 10000, 'category' => 'Snack', 'stock' => 104, 'unit' => 'pcs'],
                ['name' => 'Piattos Snack Attack 40gr', 'purchase_price' => 4000, 'code' => 'PRD-062', 'selling_price' => 6000, 'category' => 'Snack', 'stock' => 62, 'unit' => 'pcs'],
                ['name' => 'Cheetos Lite Jagung 60gr', 'purchase_price' => 7000, 'code' => 'PRD-063', 'selling_price' => 10000, 'category' => 'Snack', 'stock' => 124, 'unit' => 'pcs'],
                ['name' => 'Maxi Corn Jagung Bakar 60gr', 'purchase_price' => 5500, 'code' => 'PRD-064', 'selling_price' => 8000, 'category' => 'Snack', 'stock' => 58, 'unit' => 'pcs'],
                ['name' => 'Oreo Original 137gr', 'purchase_price' => 9000, 'code' => 'PRD-065', 'selling_price' => 13000, 'category' => 'Snack', 'stock' => 150, 'unit' => 'pcs'],
                ['name' => 'Oreo Strawberry 137gr', 'purchase_price' => 9000, 'code' => 'PRD-066', 'selling_price' => 13000, 'category' => 'Snack', 'stock' => 11, 'unit' => 'pcs'],
                ['name' => 'Roma Kelapa 300gr', 'purchase_price' => 12000, 'code' => 'PRD-067', 'selling_price' => 16000, 'category' => 'Snack', 'stock' => 141, 'unit' => 'pcs'],
                ['name' => 'Malkist Crackers 135gr', 'purchase_price' => 9000, 'code' => 'PRD-068', 'selling_price' => 13000, 'category' => 'Snack', 'stock' => 18, 'unit' => 'pcs'],
                ['name' => 'Malkist Coklat 135gr', 'purchase_price' => 9000, 'code' => 'PRD-069', 'selling_price' => 13000, 'category' => 'Snack', 'stock' => 41, 'unit' => 'pcs'],
                ['name' => 'Ovento Wafer Coklat', 'purchase_price' => 4000, 'code' => 'PRD-070', 'selling_price' => 6000, 'category' => 'Snack', 'stock' => 16, 'unit' => 'pcs'],
                ['name' => 'Kit Kat 2 Finger 17gr', 'purchase_price' => 7500, 'code' => 'PRD-071', 'selling_price' => 11000, 'category' => 'Snack', 'stock' => 141, 'unit' => 'pcs'],
                ['name' => 'Kit Kat 4 Finger 41.5gr', 'purchase_price' => 12000, 'code' => 'PRD-072', 'selling_price' => 16000, 'category' => 'Snack', 'stock' => 55, 'unit' => 'pcs'],
                ['name' => 'Beng-Beng Maxi', 'purchase_price' => 3500, 'code' => 'PRD-073', 'selling_price' => 5000, 'category' => 'Snack', 'stock' => 91, 'unit' => 'pcs'],
                ['name' => 'Pillows Coklat 150gr', 'purchase_price' => 8000, 'code' => 'PRD-074', 'selling_price' => 11000, 'category' => 'Snack', 'stock' => 52, 'unit' => 'pcs'],
                ['name' => 'Pillows Strawberry 150gr', 'purchase_price' => 8000, 'code' => 'PRD-075', 'selling_price' => 11000, 'category' => 'Snack', 'stock' => 142, 'unit' => 'pcs'],
                ['name' => 'Sukro Original 90gr', 'purchase_price' => 5000, 'code' => 'PRD-076', 'selling_price' => 7000, 'category' => 'Snack', 'stock' => 118, 'unit' => 'pcs'],
                ['name' => 'Sukro Pedas 90gr', 'purchase_price' => 5000, 'code' => 'PRD-077', 'selling_price' => 7000, 'category' => 'Snack', 'stock' => 63, 'unit' => 'pcs'],
                ['name' => 'Kacang Garuda Garing 100gr', 'purchase_price' => 8000, 'code' => 'PRD-078', 'selling_price' => 11000, 'category' => 'Snack', 'stock' => 125, 'unit' => 'pcs'],
                ['name' => 'Kacang Garuda Pedas 100gr', 'purchase_price' => 8000, 'code' => 'PRD-079', 'selling_price' => 11000, 'category' => 'Snack', 'stock' => 81, 'unit' => 'pcs'],
                ['name' => 'Popcorn Caramel 45gr', 'purchase_price' => 4000, 'code' => 'PRD-080', 'selling_price' => 6000, 'category' => 'Snack', 'stock' => 88, 'unit' => 'pcs'],
                ['name' => 'Toss Roni Crispy Macaroni 60gr', 'purchase_price' => 5000, 'code' => 'PRD-081', 'selling_price' => 7000, 'category' => 'Snack', 'stock' => 38, 'unit' => 'pcs'],
                ['name' => 'Kerupuk Bawang 250gr', 'purchase_price' => 8000, 'code' => 'PRD-082', 'selling_price' => 12000, 'category' => 'Snack', 'stock' => 101, 'unit' => 'pak'],
                ['name' => 'Kerupuk Udang 200gr', 'purchase_price' => 9000, 'code' => 'PRD-083', 'selling_price' => 13000, 'category' => 'Snack', 'stock' => 77, 'unit' => 'pak'],
                ['name' => 'Keripik Tempe 200gr', 'purchase_price' => 10000, 'code' => 'PRD-084', 'selling_price' => 15000, 'category' => 'Snack', 'stock' => 143, 'unit' => 'pak'],
                ['name' => 'Emping Melinjo 250gr', 'purchase_price' => 15000, 'code' => 'PRD-085', 'selling_price' => 20000, 'category' => 'Snack', 'stock' => 136, 'unit' => 'pak'],
                ['name' => 'Keripik Singkong Original 200gr', 'purchase_price' => 8000, 'code' => 'PRD-086', 'selling_price' => 12000, 'category' => 'Snack', 'stock' => 66, 'unit' => 'pak'],
                ['name' => 'Keripik Singkong Pedas 200gr', 'purchase_price' => 8000, 'code' => 'PRD-087', 'selling_price' => 12000, 'category' => 'Snack', 'stock' => 115, 'unit' => 'pak'],
                ['name' => 'Marning Jagung 200gr', 'purchase_price' => 7000, 'code' => 'PRD-088', 'selling_price' => 10000, 'category' => 'Snack', 'stock' => 144, 'unit' => 'pak'],
                ['name' => 'Kembang Goyang 200gr', 'purchase_price' => 10000, 'code' => 'PRD-089', 'selling_price' => 15000, 'category' => 'Snack', 'stock' => 69, 'unit' => 'pak'],
                ['name' => 'Rengginang 200gr', 'purchase_price' => 9000, 'code' => 'PRD-090', 'selling_price' => 13000, 'category' => 'Snack', 'stock' => 14, 'unit' => 'pak'],
                ['name' => 'Opak Singkong 200gr', 'purchase_price' => 8000, 'code' => 'PRD-091', 'selling_price' => 12000, 'category' => 'Snack', 'stock' => 28, 'unit' => 'pak'],
                ['name' => 'Qtela Singkong Keju 68gr', 'purchase_price' => 6000, 'code' => 'PRD-092', 'selling_price' => 9000, 'category' => 'Snack', 'stock' => 35, 'unit' => 'pcs'],
                ['name' => 'Qtela Singkong Pedas 68gr', 'purchase_price' => 6000, 'code' => 'PRD-093', 'selling_price' => 9000, 'category' => 'Snack', 'stock' => 16, 'unit' => 'pcs'],
                ['name' => 'Doritos Nacho Cheese 68gr', 'purchase_price' => 9000, 'code' => 'PRD-094', 'selling_price' => 13000, 'category' => 'Snack', 'stock' => 27, 'unit' => 'pcs'],
                ['name' => 'Lays Original 68gr', 'purchase_price' => 9000, 'code' => 'PRD-095', 'selling_price' => 13000, 'category' => 'Snack', 'stock' => 72, 'unit' => 'pcs'],
                ['name' => 'Gudang Garam Surya 12 Pro Mild', 'purchase_price' => 22000, 'code' => 'PRD-096', 'selling_price' => 26000, 'category' => 'Rokok', 'stock' => 32, 'unit' => 'pak'],
                ['name' => 'Gudang Garam Surya 16', 'purchase_price' => 26000, 'code' => 'PRD-097', 'selling_price' => 30000, 'category' => 'Rokok', 'stock' => 10, 'unit' => 'pak'],
                ['name' => 'Gudang Garam International 12', 'purchase_price' => 22000, 'code' => 'PRD-098', 'selling_price' => 26000, 'category' => 'Rokok', 'stock' => 34, 'unit' => 'pak'],
                ['name' => 'Gudang Garam Merah 12', 'purchase_price' => 18000, 'code' => 'PRD-099', 'selling_price' => 22000, 'category' => 'Rokok', 'stock' => 30, 'unit' => 'pak'],
                ['name' => 'Gudang Garam Filter 12', 'purchase_price' => 18000, 'code' => 'PRD-100', 'selling_price' => 22000, 'category' => 'Rokok', 'stock' => 12, 'unit' => 'pak'],
                ['name' => 'Djarum Super 12', 'purchase_price' => 22000, 'code' => 'PRD-101', 'selling_price' => 26000, 'category' => 'Rokok', 'stock' => 19, 'unit' => 'pak'],
                ['name' => 'Djarum Black 12', 'purchase_price' => 22000, 'code' => 'PRD-102', 'selling_price' => 26000, 'category' => 'Rokok', 'stock' => 46, 'unit' => 'pak'],
                ['name' => 'Djarum Coklat 12', 'purchase_price' => 18000, 'code' => 'PRD-103', 'selling_price' => 22000, 'category' => 'Rokok', 'stock' => 29, 'unit' => 'pak'],
                ['name' => 'Djarum MLD 12', 'purchase_price' => 22000, 'code' => 'PRD-104', 'selling_price' => 26000, 'category' => 'Rokok', 'stock' => 50, 'unit' => 'pak'],
                ['name' => 'Djarum 76 12', 'purchase_price' => 18000, 'code' => 'PRD-105', 'selling_price' => 22000, 'category' => 'Rokok', 'stock' => 8, 'unit' => 'pak'],
                ['name' => 'Sampoerna A Mild 16', 'purchase_price' => 26000, 'code' => 'PRD-106', 'selling_price' => 30000, 'category' => 'Rokok', 'stock' => 12, 'unit' => 'pak'],
                ['name' => 'Sampoerna A Mild 12', 'purchase_price' => 20000, 'code' => 'PRD-107', 'selling_price' => 24000, 'category' => 'Rokok', 'stock' => 43, 'unit' => 'pak'],
                ['name' => 'Sampoerna Hijau 12', 'purchase_price' => 18000, 'code' => 'PRD-108', 'selling_price' => 22000, 'category' => 'Rokok', 'stock' => 24, 'unit' => 'pak'],
                ['name' => 'Sampoerna Kretek 12', 'purchase_price' => 18000, 'code' => 'PRD-109', 'selling_price' => 22000, 'category' => 'Rokok', 'stock' => 16, 'unit' => 'pak'],
                ['name' => 'Philip Morris Bold 16', 'purchase_price' => 24000, 'code' => 'PRD-110', 'selling_price' => 28000, 'category' => 'Rokok', 'stock' => 49, 'unit' => 'pak'],
                ['name' => 'Bentoel Biru 12', 'purchase_price' => 17000, 'code' => 'PRD-111', 'selling_price' => 21000, 'category' => 'Rokok', 'stock' => 16, 'unit' => 'pak'],
                ['name' => 'Bentoel Merah 12', 'purchase_price' => 17000, 'code' => 'PRD-112', 'selling_price' => 21000, 'category' => 'Rokok', 'stock' => 28, 'unit' => 'pak'],
                ['name' => 'Star Mild 16', 'purchase_price' => 22000, 'code' => 'PRD-113', 'selling_price' => 26000, 'category' => 'Rokok', 'stock' => 7, 'unit' => 'pak'],
                ['name' => 'Neo Mild 16', 'purchase_price' => 20000, 'code' => 'PRD-114', 'selling_price' => 24000, 'category' => 'Rokok', 'stock' => 28, 'unit' => 'pak'],
                ['name' => 'Clas Mild 16', 'purchase_price' => 22000, 'code' => 'PRD-115', 'selling_price' => 26000, 'category' => 'Rokok', 'stock' => 31, 'unit' => 'pak'],
                ['name' => 'LA Bold 16', 'purchase_price' => 24000, 'code' => 'PRD-116', 'selling_price' => 28000, 'category' => 'Rokok', 'stock' => 23, 'unit' => 'pak'],
                ['name' => 'LA Light 16', 'purchase_price' => 24000, 'code' => 'PRD-117', 'selling_price' => 28000, 'category' => 'Rokok', 'stock' => 24, 'unit' => 'pak'],
                ['name' => 'Marlboro Red 20', 'purchase_price' => 30000, 'code' => 'PRD-118', 'selling_price' => 36000, 'category' => 'Rokok', 'stock' => 22, 'unit' => 'pak'],
                ['name' => 'Marlboro Black 20', 'purchase_price' => 30000, 'code' => 'PRD-119', 'selling_price' => 36000, 'category' => 'Rokok', 'stock' => 29, 'unit' => 'pak'],
                ['name' => 'Dunhill Fine Cut 16', 'purchase_price' => 28000, 'code' => 'PRD-120', 'selling_price' => 34000, 'category' => 'Rokok', 'stock' => 7, 'unit' => 'pak'],
            ];

            foreach ($products as $item) {
                // Mapping penamaan kategori Excel agar sesuai dengan nama di CategorySeeder
                $mappedCategoryName = match ($item['category']) {
                    'Snack'      => 'Makanan Ringan',
                    'Kopi & Teh' => 'Minuman',
                    default      => $item['category'], // Tetap gunakan aslinya (Minuman, Produk Susu, Rokok)
                };

                // Cari ID berdasarkan nama kategori yang sudah di-mapping
                $category = Category::where('name', $mappedCategoryName)->first();

                // Insert jika kategorinya ditemukan
                if ($category) {
                    Product::create([
                        'category_id'    => $category->id,
                        'code'           => $item['code'],
                        'name'           => $item['name'],
                        'unit'           => $item['unit'],
                        'purchase_price' => $item['purchase_price'],
                        'selling_price'  => $item['selling_price'],
                        'stock'          => $item['stock'],
                        'photo'          => null,
                    ]);
                }
            }
        }
    }