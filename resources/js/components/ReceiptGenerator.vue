<script setup lang="ts">
import { jsPDF } from "jspdf";
import { ref } from "vue";
import { defineProps } from "vue";
import type { Transaction } from "@/types";
import { Receipt, Printer, FileText, X, Plus } from "lucide-vue-next";

const props = defineProps<{
    transaction: Transaction;
}>();

const showModal = ref(false);

// Mempersingkat ID - mengambil 6 karakter terakhir dari UUID
const shortenId = (id: string) => {
    return id.toString().slice(-6);
};

const generateThermalReceipt = (includeTax = false) => {
    const transaction = props.transaction;
    const doc = new jsPDF({
        unit: "mm",
        format: [80, 180], // Format untuk thermal printer (80mm width)
    });

    const pageWidth = doc.internal.pageSize.width;
    const margin = 5;
    const shortId = shortenId(transaction.id);

    // Header - Thermal Receipt
    doc.setFontSize(14);
    doc.setFont("helvetica", "bold");
    doc.text("PRINT ONLINE", pageWidth / 2, 10, { align: "center" });
    doc.setFontSize(8);
    doc.text("----------------------------------", pageWidth / 2, 15, { align: "center" });
    doc.setFontSize(10);
    doc.text("CASH RECEIPT", pageWidth / 2, 22, { align: "center" });
    doc.setFontSize(8);
    doc.text("----------------------------------", pageWidth / 2, 27, { align: "center" });

    // Info Transaksi
    doc.setFont("helvetica", "normal");
    let yPos = 35;

    doc.text(`Trans #: ${shortId}`, margin, yPos);
    doc.text(`Date: ${new Date(transaction.transaction_date).toLocaleDateString()}`, pageWidth - margin, yPos, { align: "right" });
    yPos += 7;
    doc.text(`Customer: ${transaction.customer_name}`, margin, yPos);
    yPos += 10;

    // Item details header
    doc.text("----------------------------------", pageWidth / 2, yPos, { align: "center" });
    yPos += 7;

    // Items
    let subtotal = 0;
    transaction.items.forEach((item) => {
        // Item name and quantity more prominently
        doc.setFont("helvetica", "bold");
        doc.text(`${item.name} (Qty: ${item.quantity})`, margin, yPos);
        yPos += 5;

        // Price details
        doc.setFont("helvetica", "normal");
        const itemTotal = item.quantity * item.price_per_unit;
        subtotal += itemTotal;
        doc.text(`${item.quantity} x ${item.price_per_unit.toLocaleString('id-ID')}`, margin, yPos);
        doc.text(`${itemTotal.toLocaleString('id-ID')}`, pageWidth - margin, yPos, { align: "right" });
        yPos += 7;

        // Fields/options
        item.fields.forEach((field) => {
            const fieldTotal = field.price * field.quantity;
            doc.text(`  ${field.name} (Qty: ${field.quantity})`, margin, yPos);
            doc.text(`${fieldTotal.toLocaleString('id-ID')}`, pageWidth - margin, yPos, { align: "right" });
            yPos += 5;
        });

        yPos += 2;
    });

    // Separator
    doc.text("----------------------------------", pageWidth / 2, yPos, { align: "center" });
    yPos += 10;

    // Subtotal
    doc.text("Subtotal", margin, yPos);
    doc.text(`${subtotal.toLocaleString('id-ID')}`, pageWidth - margin, yPos, { align: "right" });
    yPos += 7;

    // Tax if included
    let totalAmount = subtotal;
    if (includeTax) {
        const tax = Math.round(subtotal * 0.11); // 11% tax
        totalAmount = subtotal + tax;
        doc.text("Tax (11%)", margin, yPos);
        doc.text(`${tax.toLocaleString('id-ID')}`, pageWidth - margin, yPos, { align: "right" });
        yPos += 7;
    }

    // Total
    doc.setFont("helvetica", "bold");
    doc.text("TOTAL", margin, yPos);
    doc.text(`Rp ${totalAmount.toLocaleString('id-ID')}`, pageWidth - margin, yPos, { align: "right" });
    yPos += 15;

    // Footer
    doc.setFontSize(8);
    doc.setFont("helvetica", "normal");
    doc.text("Terima Kasih Atas Kunjungan Anda", pageWidth / 2, yPos, { align: "center" });
    yPos += 5;
    doc.text("www.printonline.co.id", pageWidth / 2, yPos, { align: "center" });

    // Simpan PDF - nama file sesuai dengan jenis receipt
    const taxText = includeTax ? "_with_tax" : "";
    doc.save(`thermal_receipt${taxText}_${shortId}.pdf`);

    // Tutup modal setelah generate receipt
    showModal.value = false;
};

const generateStandardReceipt = (includeTax = false) => {
    const transaction = props.transaction;
    const doc = new jsPDF({
        unit: "mm",
        format: "a4",
    });

    const pageWidth = doc.internal.pageSize.width;
    const margin = 20;
    const shortId = shortenId(transaction.id);

    // Header
    doc.setFontSize(20);
    doc.setFont("helvetica", "bold");
    doc.text("RECEIPT", pageWidth / 2, 20, { align: "center" });

    // Company Info
    doc.setFontSize(12);
    doc.setFont("helvetica", "normal");
    doc.text("Print Online", margin, 35);
    doc.text("123 Main Street, Jakarta, ID 12345", margin, 41);
    doc.text("(021) 123-4567 | contact@printonline.co.id", margin, 47);

    // Line separator
    doc.setDrawColor(100, 100, 100);
    doc.line(margin, 50, pageWidth - margin, 50);

    // Receipt Info
    let yPos = 60;
    doc.setFillColor(240, 240, 240);
    doc.rect(margin, yPos, pageWidth - (margin * 2), 25, "F");

    doc.setFont("helvetica", "bold");
    doc.text("RECEIPT NO:", margin + 5, yPos + 8);
    doc.text("DATE:", margin + 5, yPos + 18);

    doc.setFont("helvetica", "normal");
    doc.text(shortId, margin + 40, yPos + 8);
    doc.text(new Date(transaction.transaction_date).toLocaleDateString(), margin + 40, yPos + 18);

    doc.setFont("helvetica", "bold");
    doc.text("CUSTOMER:", pageWidth - margin - 65, yPos + 8);
    doc.setFont("helvetica", "normal");
    doc.text(transaction.customer_name, pageWidth - margin - 5, yPos + 8, { align: "right" });

    // Table header
    yPos += 35;
    doc.setFillColor(230, 230, 230);
    doc.rect(margin, yPos, pageWidth - (margin * 2), 10, "F");

    doc.setFont("helvetica", "bold");
    doc.text("DESCRIPTION", margin + 5, yPos + 7);
    doc.text("QTY", pageWidth - margin - 75, yPos + 7);
    doc.text("PRICE", pageWidth - margin - 45, yPos + 7);
    doc.text("AMOUNT", pageWidth - margin - 10, yPos + 7, { align: "right" });

    // Table content
    yPos += 15;
    doc.setFont("helvetica", "normal");

    let subtotal = 0;
    transaction.items.forEach((item) => {
        const itemTotal = item.quantity * item.price_per_unit;
        subtotal += itemTotal;

        doc.setFont("helvetica", "semibold");
        // Include quantity in the item name for better visibility
        doc.text(`${item.name} (Qty: ${item.quantity})`, margin + 5, yPos);

        doc.setFont("helvetica", "normal");
        doc.text(item.quantity.toString(), pageWidth - margin - 75, yPos);
        doc.text(item.price_per_unit.toLocaleString('id-ID'), pageWidth - margin - 45, yPos);
        doc.text(itemTotal.toLocaleString('id-ID'), pageWidth - margin - 10, yPos, { align: "right" });

        yPos += 8;

        // Add fields as sub-items
        item.fields.forEach(field => {
            const fieldTotal = field.price * field.quantity;

            // Include field quantity in the description
            doc.text(`  - ${field.name} (Qty: ${field.quantity})`, margin + 5, yPos);
            doc.text(field.quantity.toString(), pageWidth - margin - 75, yPos);
            doc.text(field.price.toLocaleString('id-ID'), pageWidth - margin - 45, yPos);
            doc.text(fieldTotal.toLocaleString('id-ID'), pageWidth - margin - 10, yPos, { align: "right" });

            yPos += 8;
        });

        yPos += 4;
    });

    // Line separator before totals
    doc.setDrawColor(200, 200, 200);
    doc.line(margin, yPos, pageWidth - margin, yPos);
    yPos += 10;

    // Subtotal
    doc.text("Subtotal", pageWidth - margin - 45, yPos);
    doc.text(`${subtotal.toLocaleString('id-ID')}`, pageWidth - margin - 10, yPos, { align: "right" });
    yPos += 8;

    // Tax if included
    let totalAmount = subtotal;
    if (includeTax) {
        const tax = Math.round(subtotal * 0.11); // 11% tax
        totalAmount = subtotal + tax;
        doc.text("Tax (11%)", pageWidth - margin - 45, yPos);
        doc.text(`${tax.toLocaleString('id-ID')}`, pageWidth - margin - 10, yPos, { align: "right" });
        yPos += 8;
    }

    // Total
    doc.setDrawColor(100, 100, 100);
    doc.line(pageWidth - margin - 70, yPos - 2, pageWidth - margin, yPos - 2);
    doc.setFont("helvetica", "bold");
    doc.text("TOTAL", pageWidth - margin - 45, yPos + 6);
    doc.text(`Rp ${totalAmount.toLocaleString('id-ID')}`, pageWidth - margin - 10, yPos + 6, { align: "right" });

    // Thank you message
    yPos += 30;
    doc.setFont("helvetica", "bold");
    doc.text("THANK YOU FOR YOUR BUSINESS", pageWidth / 2, yPos, { align: "center" });

    // Footer
    yPos += 10;
    doc.setFont("helvetica", "normal");
    doc.setFontSize(9);
    doc.text("For questions about this receipt, please contact us at contact@printonline.co.id", pageWidth / 2, yPos, { align: "center" });
    yPos += 5;
    doc.text("www.printonline.co.id", pageWidth / 2, yPos, { align: "center" });

    // Simpan PDF - nama file sesuai dengan jenis receipt
    const taxText = includeTax ? "_with_tax" : "";
    doc.save(`a4_receipt${taxText}_${shortId}.pdf`);

    // Tutup modal setelah generate receipt
    showModal.value = false;
};
</script>

<template>
    <div>
        <!-- Tombol untuk membuka modal -->
        <button @click="showModal = true" class="bg-green-600 hover:bg-green-700 px-2 py-1 text-white rounded"
            title="Receipt">
            <Receipt class="w-4 h-4" />
        </button>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md shadow-xl">
                <!-- Header Modal -->
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Pilih Jenis Receipt</h3>
                    <button @click="showModal = false" class="text-gray-500 hover:text-gray-700">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <!-- Preview & Options Grid -->
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <!-- Thermal Receipt - No Tax -->
                    <div class="border rounded-md p-3 hover:bg-gray-50 cursor-pointer"
                        @click="generateThermalReceipt(false)">
                        <div class="flex items-center justify-center mb-2">
                            <Printer class="w-6 h-6 text-blue-600" />
                        </div>
                        <h4 class="text-center text-sm font-medium">Thermal Receipt</h4>
                        <p class="text-center text-xs text-gray-500 mt-1">Tanpa Pajak</p>
                    </div>

                    <!-- Thermal Receipt - With Tax -->
                    <div class="border rounded-md p-3 hover:bg-gray-50 cursor-pointer"
                        @click="generateThermalReceipt(true)">
                        <div class="flex items-center justify-center mb-2">
                            <Printer class="w-6 h-6 text-blue-600" />
                            <Plus class="w-3 h-3 text-blue-600 -ml-1" />
                        </div>
                        <h4 class="text-center text-sm font-medium">Thermal Receipt</h4>
                        <p class="text-center text-xs text-gray-500 mt-1">Dengan Pajak</p>
                    </div>

                    <!-- A4 Receipt - No Tax -->
                    <div class="border rounded-md p-3 hover:bg-gray-50 cursor-pointer"
                        @click="generateStandardReceipt(false)">
                        <div class="flex items-center justify-center mb-2">
                            <FileText class="w-6 h-6 text-green-600" />
                        </div>
                        <h4 class="text-center text-sm font-medium">A4 Receipt</h4>
                        <p class="text-center text-xs text-gray-500 mt-1">Tanpa Pajak</p>
                    </div>

                    <!-- A4 Receipt - With Tax -->
                    <div class="border rounded-md p-3 hover:bg-gray-50 cursor-pointer"
                        @click="generateStandardReceipt(true)">
                        <div class="flex items-center justify-center mb-2">
                            <FileText class="w-6 h-6 text-green-600" />
                            <Plus class="w-3 h-3 text-green-600 -ml-1" />
                        </div>
                        <h4 class="text-center text-sm font-medium">A4 Receipt</h4>
                        <p class="text-center text-xs text-gray-500 mt-1">Dengan Pajak</p>
                    </div>
                </div>

                <!-- Footer Modal -->
                <div class="mt-4 text-center text-xs text-gray-500">
                    Klik opsi di atas untuk mengunduh receipt dalam format PDF
                </div>
            </div>
        </div>
    </div>
</template>