@extends('layouts.store')
@section('title', 'Store QR')

@section('content')
    <div class="max-w-xl">
        <h1 class="font-display font-bold text-4xl text-slate-900 tracking-tight">Store QR code</h1>
        <p class="text-slate-500 mt-3 leading-relaxed">
            Customers scan this in the store to order from their phone. Print it and put it by the counter.
        </p>

        <div class="mt-10">
            <div class="w-64 h-64 bg-white border border-slate-200/80 rounded-2xl flex items-center justify-center shadow-sm">
                <div id="qrcode"></div>
            </div>
            <p class="mono text-sm text-slate-500 mt-4">{{ $scanUrl }}</p>

            <button type="button" onclick="downloadQrPdf()"
                class="mt-5 inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 text-sm font-bold hover:bg-slate-50 transition">
                <i data-lucide="download" class="w-4 h-4"></i>
                Download as PDF
            </button>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
        new QRCode(document.getElementById('qrcode'), {
            text: @json($scanUrl),
            width: 224, height: 224,
            colorDark: '#16211a', colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H,
        });

        function downloadQrPdf() {
            try {
                const holder = document.getElementById('qrcode');
                const canvas = holder.querySelector('canvas');
                const img = holder.querySelector('img');
                const dataUrl = canvas ? canvas.toDataURL('image/png') : (img ? img.src : null);
                if (!dataUrl || !window.jspdf) return window.print();

                const { jsPDF } = window.jspdf;
                const doc = new jsPDF({ unit: 'pt', format: 'a4' });
                const pageW = doc.internal.pageSize.getWidth();

                doc.setFont('helvetica', 'bold'); doc.setFontSize(24);
                doc.text(@json($store->store_name), pageW / 2, 90, { align: 'center' });
                doc.setFont('helvetica', 'normal'); doc.setFontSize(13); doc.setTextColor(120);
                doc.text('Store #' + @json($store->store_code), pageW / 2, 115, { align: 'center' });

                const qr = 300;
                doc.addImage(dataUrl, 'PNG', (pageW - qr) / 2, 150, qr, qr);

                doc.setFont('helvetica', 'bold'); doc.setFontSize(15); doc.setTextColor(20);
                doc.text('Scan to start ordering', pageW / 2, 500, { align: 'center' });
                doc.setFont('helvetica', 'normal'); doc.setFontSize(11); doc.setTextColor(120);
                doc.text(@json($scanUrl), pageW / 2, 522, { align: 'center' });

                doc.save('Store-QR-' + @json($store->store_code) + '.pdf');
            } catch (e) { window.print(); }
        }
    </script>
@endpush
