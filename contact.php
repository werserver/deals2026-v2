<?php include_once 'includes/header.php'; ?>
<main class="container mx-auto px-4 py-12 max-w-3xl">
    <div class="text-center space-y-4 mb-12">
        <h1 class="text-4xl font-bold text-gray-900">ติดต่อเรา</h1>
        <p class="text-gray-500 text-lg">มีคำถามหรือข้อเสนอแนะ? ทีมงานของเราพร้อมรับฟังคุณ</p>
    </div>

    <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-xl animate-fade-in">
        <form class="space-y-6" onsubmit="event.preventDefault(); alert('ส่งข้อความเรียบร้อยแล้ว (ตัวอย่าง)');">
            <div class="grid gap-6 sm:grid-cols-2">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-700">ชื่อของคุณ</label>
                    <input type="text" required
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:ring-2 focus:ring-primary focus:bg-white outline-none transition-all" 
                           placeholder="ระบุชื่อ">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-700">อีเมล</label>
                    <input type="email" required
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:ring-2 focus:ring-primary focus:bg-white outline-none transition-all" 
                           placeholder="example@mail.com">
                </div>
            </div>
            <div class="space-y-2">
                <label class="text-sm font-medium text-gray-700">หัวข้อ</label>
                <input type="text" required
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:ring-2 focus:ring-primary focus:bg-white outline-none transition-all" 
                       placeholder="ระบุหัวข้อที่ต้องการติดต่อ">
            </div>
            <div class="space-y-2">
                <label class="text-sm font-medium text-gray-700">ข้อความ</label>
                <textarea required
                          class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:ring-2 focus:ring-primary focus:bg-white outline-none min-h-[150px] transition-all" 
                          placeholder="ระบุรายละเอียดข้อความของคุณ"></textarea>
            </div>
            <button type="submit" class="w-full py-4 bg-primary text-white rounded-2xl text-lg font-bold hover:bg-red-600 transition-all shadow-lg shadow-red-200">
                ส่งข้อความ
            </button>
        </form>
    </div>
</main>
<?php include_once 'includes/footer.php'; ?>
