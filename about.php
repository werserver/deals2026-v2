<?php include_once 'includes/header.php'; ?>
<main class="container mx-auto px-4 py-12 max-w-4xl">
    <div class="text-center space-y-4 mb-12">
        <h1 class="text-4xl font-bold text-gray-900">เกี่ยวกับเรา</h1>
        <p class="text-gray-500 text-lg">เราคือผู้นำด้านการรวบรวมดีลและโปรโมชั่นสินค้าออนไลน์</p>
    </div>

    <div class="grid gap-8 md:grid-cols-2 mb-16">
        <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm space-y-4">
            <div class="h-12 w-12 bg-red-50 rounded-2xl flex items-center justify-center text-primary">
                <i class="fas fa-shield-alt text-2xl"></i>
            </div>
            <h2 class="text-xl font-bold">ความน่าเชื่อถือ</h2>
            <p class="text-gray-500">เราคัดสรรเฉพาะสินค้าจากร้านค้าที่เป็นทางการและได้รับความนิยม เพื่อให้คุณมั่นใจในคุณภาพสินค้าทุกชิ้น</p>
        </div>
        <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm space-y-4">
            <div class="h-12 w-12 bg-red-50 rounded-2xl flex items-center justify-center text-primary">
                <i class="fas fa-star text-2xl"></i>
            </div>
            <h2 class="text-xl font-bold">ดีลที่ดีที่สุด</h2>
            <p class="text-gray-500">ทีมงานของเราอัปเดตข้อมูลราคาและส่วนลดทุกวัน เพื่อให้คุณได้รับข้อเสนอที่คุ้มค่าที่สุดในตลาด</p>
        </div>
    </div>

    <div class="prose prose-slate max-w-none">
        <h2 class="text-2xl font-bold mb-4">วิสัยทัศน์ของเรา</h2>
        <p class="text-gray-500 leading-relaxed mb-6">
            <?php echo htmlspecialchars($config['siteName']); ?> ก่อตั้งขึ้นด้วยความตั้งใจที่จะช่วยให้ผู้บริโภคชาวไทยสามารถเข้าถึงสินค้าคุณภาพในราคาที่ประหยัดที่สุด 
            เราเชื่อว่าการช้อปปิ้งออนไลน์ควรเป็นเรื่องง่าย สนุก และคุ้มค่า เราจึงสร้างแพลตฟอร์มนี้ขึ้นมาเพื่อเป็นตัวกลางในการค้นหาและเปรียบเทียบดีลที่ดีที่สุดจากทุกร้านค้าชั้นนำ
        </p>
    </div>
</main>
<?php include_once 'includes/footer.php'; ?>
