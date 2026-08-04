@extends('layouts.app')

@push('styles')
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js"></script>
<style>
    .editor-container {
        display: flex;
        flex-direction: column;
        min-height: calc(100vh - 80px); /* Adjust based on your header */
        background: #f3f4f6;
        width: 100%;
    }
    @media (min-width: 1024px) {
        .editor-container {
            flex-direction: row;
        }
    }
    .canvas-wrapper {
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 1.5rem;
        overflow: auto;
        min-width: 0; /* fixes flex flex-1 scroll bugs */
    }
    .canvas-container-inner {
        background: #ffffff;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        border-radius: 0.5rem;
        overflow: hidden;
    }
    .tools-sidebar {
        width: 100%;
        flex-shrink: 0;
        background: #ffffff;
        border-top: 1px solid #e5e7eb;
        padding: 1.5rem;
        overflow-y: auto;
    }
    @media (min-width: 1024px) {
        .tools-sidebar {
            width: 320px;
            border-top: none;
            border-left: 1px solid #e5e7eb;
            height: calc(100vh - 80px);
        }
    }
</style>
@endpush

@section('content')
<div class="editor-container">
    <!-- Main Canvas Area -->
    <main class="canvas-wrapper">
        <div class="canvas-container-inner">
            <canvas id="editorCanvas"></canvas>
        </div>
    </main>

    <!-- Sidebar Tools -->
    <aside class="tools-sidebar">
        <div class="mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
            <h2 class="text-lg font-bold text-gray-900 flex-1">  Design</h2>
            
            <div class="flex items-center gap-3 w-full md:w-auto">
                <button id="downloadBtn" disabled onclick="downloadDesign()" title="Download Design" class="p-2 border border-gray-300 text-gray-700 bg-gray-200 opacity-50 cursor-not-allowed rounded-lg hover:bg-gray-50 flexItems-center justify-center transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                </button>
                <button id="cartBtn" disabled onclick="openAddToCartModal()" class="flex-1 md:flex-none bg-brand-500 text-white px-5 py-2 rounded-lg font-medium hover:bg-brand-primary/90 transition-colors flex items-center justify-center gap-2 opacity-50 cursor-not-allowed">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                    Add to Cart
                </button>
            </div>
        </div>

        <!-- Tool panel contents (Text, Colors, etc.) -->
        <div id="textTools" class="hidden mb-6 bg-gray-50 p-4 rounded-xl border border-gray-100">
            <h3 class="text-sm font-bold text-gray-700 uppercase mb-3 tracking-wide">Text Properties</h3>
            
            <div class="space-y-3">
                <!-- Color -->
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Color</label>
                    <input type="color" id="textColor" onchange="updateTextProp('fill', this.value)" class="w-full h-8 cursor-pointer rounded border border-gray-300">
                </div>
                
                <!-- Font Family -->
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Font Family</label>
                    <select id="textFamily" onchange="updateTextProp('fontFamily', this.value)" class="w-full text-sm border-gray-300 rounded-md">
                        <option value="Arial">Arial</option>
                        <option value="Helvetica">Helvetica</option>
                        <option value="Times New Roman">Times</option>
                        <option value="Courier New">Courier</option>
                        <option value="Georgia">Georgia</option>
                    </select>
                </div>

                <!-- Font Size -->
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Font Size</label>
                    <input type="number" id="textSize" onchange="updateTextProp('fontSize', parseInt(this.value))" class="w-full text-sm border-gray-300 rounded-md">
                </div>

                <div class="pt-2">
                    <button onclick="deleteSelected()" class="w-full bg-red-50 text-red-600 font-medium py-2 rounded-lg hover:bg-red-100 transition-colors">
                        Delete Selected
                    </button>
                </div>
            </div>
        </div>

        <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100">
            <h3 class="text-sm font-bold text-blue-900 uppercase mb-2">Instructions</h3>
            <ul class="text-sm text-blue-800 space-y-2 list-disc pl-4">
                <li>Click <strong class="text-red-500">red zones</strong> to upload your photos.</li>
                <li>Drag corners of uploaded photos to resize them.</li>
                <li>Double tap text to edit the words.</li>
                <li>Use properties to change font style and colors.</li>
            </ul>
        </div>
        
        <div class="mt-6">
            <button onclick="addText()" class="w-full bg-gray-100 text-gray-800 font-medium py-2.5 rounded-lg hover:bg-gray-200 transition-colors flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                Add Custom Text
            </button>
        </div>
    </aside>
</div>

<!-- Hidden File Input for Masks -->
<input type="file" id="imageUploadInput" accept="image/*" style="display: none;" onchange="handleImageUpload(event)">

<!-- Alpine Add To Cart Modal -->
<div x-data="cartModal()" 
     id="cartModal"
     @open-cart-modal.window="openModal($event.detail)"
     class="relative z-50" 
     aria-labelledby="modal-title" 
     role="dialog" 
     aria-modal="true" 
     style="display: none;"
     x-show="isOpen">
  <!-- Backdrop -->
  <div x-show="isOpen" 
       x-transition.opacity 
       class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

  <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
      <div x-show="isOpen" 
           @click.away="closeModal"
           x-transition 
           class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
        
        <form @submit.prevent="submitToCart">
            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                        <h3 class="text-xl leading-6 font-bold text-gray-900 mb-4" id="modal-title">Complete Your Order</h3>
                        
                        <!-- Base Product Display -->
                        <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-xl mb-6">
                            <img :src="templatePreview" class="w-16 h-16 object-cover rounded shadow-sm border border-gray-200">
                            <div>
                                <h4 class="font-bold text-gray-800">{{ $product->name }}</h4>
                                <p class="text-sm text-gray-500">Base Price: <span x-text="__price(basePrice)"></span></p>
                            </div>
                        </div>

                        <!-- Options -->
                        <div class="space-y-4 mb-6">
                            @foreach($product->optionGroups as $group)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ $group->name }}</label>
                                @if($group->type === 'select')
                                    <select x-model="selectedOptions['{{ $group->id }}']" 
                                            @change="calculateTotal"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 transition-colors">
                                        @foreach($group->values as $value)
                                            <option value="{{ $value->id }}" data-price="{{ $value->active_price_modifier }}" data-type="{{ $value->price_type }}">
                                                {{ $value->label }} 
                                                @if($value->price_modifier != 0)
                                                    ({{ $value->formatted_price_modifier }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($group->values as $value)
                                            <label class="cursor-pointer">
                                                <input type="radio" 
                                                       x-model="selectedOptions['{{ $group->id }}']" 
                                                       value="{{ $value->id }}" 
                                                       data-price="{{ $value->active_price_modifier }}"
                                                       data-type="{{ $value->price_type }}"
                                                       @change="calculateTotal"
                                                       class="peer sr-only">
                                                <div class="px-4 py-2 bg-white border border-gray-200 rounded-lg peer-checked:border-brand-50 peer-checked:bg-brand-50 peer-checked:text-brand-700 hover:border-brand-300 transition-all font-medium text-sm flex items-center justify-center min-w-min">
                                                    @if($value->label)
                                                        <span>{{ $value->label }}</span>
                                                    @endif
                                                    @if($value->price_modifier != 0)
                                                        <span class="text-xs text-gray-500 ml-1">({{ $value->formatted_price_modifier }})</span>
                                                    @endif
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            @endforeach
                        </div>

                        <!-- Quantity & Total -->
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Quantity</label>
                                <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden bg-white w-28">
                                    <button type="button" @click="if(quantity > 1) { quantity--; calculateTotal(); }" class="px-3 py-1 bg-gray-50 hover:bg-gray-100 transition-colors border-r border-gray-300">-</button>
                                    <input type="number" x-model.number="quantity" @change="calculateTotal" min="1" class="w-full text-center border-none p-1 font-medium focus:ring-0">
                                    <button type="button" @click="quantity++; calculateTotal();" class="px-3 py-1 bg-gray-50 hover:bg-gray-100 transition-colors border-l border-gray-300">+</button>
                                </div>
                            </div>
                            <div class="text-right">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Total</label>
                                <span class="text-2xl font-black text-brand-600"><span x-text="__price(totalPrice)"></span></span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            
            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 border-t border-gray-200">
                <button type="submit" 
                        :disabled="isSubmitting"
                        class="inline-flex w-full justify-center rounded-xl bg-brand-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-brand-500 transition-colors sm:ml-3 sm:w-auto items-center gap-2">
                    <svg x-show="isSubmitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span x-text="isSubmitting ? 'Processing...' : 'Add to Cart'"></span>
                </button>
                <button type="button" @click="closeModal" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-6 py-3 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                    Cancel
                </button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
    const CANVAS_W = 600;
    let canvasH = 600;
    let canvas;
    let frameImage;
    
    // Loaded Data
    const productData = @json($product);
    const frameUrl = '{{ $product->frame_image_url }}';
    
    let activeMaskZone = null;

    document.addEventListener('DOMContentLoaded', () => {
        initEditor();

        // Add keyboard delete support
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Delete' || e.key === 'Backspace') {
                // Ignore if editing text
                if (e.target.tagName.toLowerCase() === 'input' || e.target.tagName.toLowerCase() === 'textarea') return;
                const activeObj = canvas?.getActiveObject();
                if (activeObj && !activeObj.isEditing) {
                    deleteSelected();
                }
            }
        });
    });

    function initEditor() {
        let maskData = productData.mask_data;
        if (typeof maskData === 'string') {
            try { maskData = JSON.parse(maskData); } catch(e) {}
        }

        // We establish a 600x600 canvas exactly like the admin
        canvas = new fabric.Canvas('editorCanvas', {
            width: CANVAS_W,
            height: canvasH, // will update
            selection: false,
            preserveObjectStacking: true
        });

        // Function to visually scale the canvas for mobile screens
        function fitCanvas() {
            const wrapper = document.querySelector('.canvas-wrapper');
            if (!wrapper || !canvas) return;
            
            const pX = 48; // 24px left + 24px right padding
            const availableWidth = wrapper.clientWidth - pX;
            
            if (availableWidth > 0 && availableWidth < CANVAS_W) {
                const scale = availableWidth / CANVAS_W;
                canvas.setDimensions({
                    width: (CANVAS_W * scale) + 'px',
                    height: (canvasH * scale) + 'px'
                }, { cssOnly: true });
            } else {
                canvas.setDimensions({
                    width: CANVAS_W + 'px',
                    height: canvasH + 'px'
                }, { cssOnly: true });
            }
            canvas.calcOffset(); // Important to fix mouse interactions when scaled visually
        }

        window.addEventListener('resize', fitCanvas);

        if (!frameUrl) return;

        // Load Frame using Fabric 5 wrapper 
        fabric.Image.fromURL(frameUrl, function(img) {
            frameImage = img;
            
            const imgW = img.width;
            const imgH = img.height;

            const adminCW = (maskData && maskData.canvasWidth) ? maskData.canvasWidth : imgW;
            const adminCH = (maskData && maskData.canvasHeight) ? maskData.canvasHeight : imgH;
            
            const adminRatio = adminCW / adminCH;
            canvasH = Math.round(CANVAS_W / adminRatio);
            
            canvas.setWidth(CANVAS_W);
            canvas.setHeight(canvasH);

            const ratio = CANVAS_W / adminCW;

            img.set({
                left: 0, top: 0,
                scaleX: CANVAS_W / imgW,
                scaleY: canvasH / imgH,
                selectable: false,
                evented: false
            });

            canvas.add(img);

            // Load Masks
            if (maskData && maskData.masks) {
                maskData.masks.forEach(m => {
                    const fLeft = m.left * ratio;
                    const fTop = m.top * ratio;
                    const fScaleX = (m.scaleX || 1) * ratio;
                    const fScaleY = (m.scaleY || 1) * ratio;

                    if (m.type === 'text') {
                        const txt = new fabric.IText(m.text || 'Text', {
                            left: fLeft, top: fTop,
                            scaleX: fScaleX, scaleY: fScaleY,
                            angle: m.angle || 0,
                            fontSize: m.fontSize || 24,
                            fontFamily: m.fontFamily || 'Arial',
                            fill: m.fill || '#000000',
                            editable: true,
                            cornerColor: '#2563eb',
                            transparentCorners: false
                        });
                        txt.setControlsVisibility({ mb: false, ml: false, mr: false, mt: false });
                        canvas.add(txt);
                    } else {
                        // Image Placeholder
                        const rawW = m.width || 100;
                        const rawH = m.height || 100;

                        const rect = new fabric.Rect({
                            width: rawW, height: rawH,
                            fill: 'rgba(220, 38, 38, 0.3)',
                            stroke: 'rgba(220, 38, 38, 0.7)',
                            strokeWidth: 4 / fScaleX,
                            rx: 4 / fScaleX, ry: 4 / fScaleY
                        });

                        const labelText = new fabric.IText('Drop/Click to Upload', {
                            fontSize: Math.max(12, rawW * 0.1),
                            fontFamily: 'Arial',
                            fill: 'rgba(180, 30, 30, 0.9)',
                            fontWeight: 'bold',
                            originX: 'center', originY: 'center',
                            left: rawW / 2, top: rawH / 2,
                            scaleX: 1 / fScaleX,
                            scaleY: 1 / fScaleY,
                            selectable: false
                        });

                        const group = new fabric.Group([rect, labelText], {
                            left: fLeft, top: fTop,
                            scaleX: fScaleX, scaleY: fScaleY,
                            angle: m.angle || 0,
                            selectable: true,
                            hasControls: false,
                            hasBorders: true,
                            borderColor: '#dc2626',
                            hoverCursor: 'pointer',
                            lockMovementX: true, lockMovementY: true
                        });

                        group._isMaskPlaceholder = true;
                        group._hasImage = false;
                        canvas.add(group);
                    }
                });
            }
            checkPlaceholders();
            fitCanvas(); // scale down if needed now that heights are calculated
        }, { crossOrigin: 'anonymous' });

        // Event listeners
        canvas.on('mouse:down', function(e) {
            const target = e.target;
            if (target && target._isMaskPlaceholder) {
                activeMaskZone = target;
                document.getElementById('imageUploadInput').click();
            }
        });

        canvas.on('selection:created', handleSelection);
        canvas.on('selection:updated', handleSelection);
        canvas.on('selection:cleared', () => {
            document.getElementById('textTools').classList.add('hidden');
        });
    }

    function handleSelection(e) {
        const obj = e.target || canvas.getActiveObject();
        if (obj && obj.type === 'i-text' && !obj._isMaskPlaceholder) {
            document.getElementById('textTools').classList.remove('hidden');
            document.getElementById('textColor').value = obj.fill || '#000000';
            document.getElementById('textFamily').value = obj.fontFamily || 'Arial';
            document.getElementById('textSize').value = obj.fontSize || 24;
        } else {
            document.getElementById('textTools').classList.add('hidden');
        }
    }

    function updateTextProp(prop, value) {
        const obj = canvas.getActiveObject();
        if (obj && obj.type === 'i-text') {
            obj.set(prop, value);
            canvas.requestRenderAll();
        }
    }

    function addText() {
        const txt = new fabric.IText('Your Text', {
            left: CANVAS_W / 2, top: canvasH / 2,
            fontSize: 24,
            fontFamily: 'Arial',
            fill: '#000000',
            originX: 'center', originY: 'center',
            cornerColor: '#2563eb',
            transparentCorners: false
        });
        canvas.add(txt);
        canvas.setActiveObject(txt);
    }

    function deleteSelected() {
        const obj = canvas.getActiveObject();
        if (obj) {
            // Restore the mask placeholder if this image was replacing one
            if (obj._boundMaskZone) {
                const mask = obj._boundMaskZone;
                mask.set({ evented: true, selectable: true });
                mask.item(0).set({ fill: 'rgba(220, 38, 38, 0.3)', stroke: 'rgba(220, 38, 38, 0.7)' }); // restore red tint layer and border
                if (mask.item(1)) mask.item(1).set({ opacity: 1 }); // show text again
                mask._hasImage = false;
            }

            canvas.remove(obj);
            canvas.discardActiveObject();
            document.getElementById('textTools').classList.add('hidden');
            checkPlaceholders();
        }
    }

    function handleImageUpload(e) {
        const file = e.target.files[0];
        if (!file || !activeMaskZone) return;

        const reader = new FileReader();
        reader.onload = function(f) {
            const tempImg = new Image();
            tempImg.onload = function() {
                const MAX_WIDTH = 1500;
                const MAX_HEIGHT = 1500;
                let width = tempImg.width;
                let height = tempImg.height;

                if (width > height) {
                    if (width > MAX_WIDTH) { height *= MAX_WIDTH / width; width = MAX_WIDTH; }
                } else {
                    if (height > MAX_HEIGHT) { width *= MAX_HEIGHT / height; height = MAX_HEIGHT; }
                }

                const uploadCanvas = document.createElement('canvas');
                uploadCanvas.width = width;
                uploadCanvas.height = height;
                const ctx = uploadCanvas.getContext('2d');
                ctx.drawImage(tempImg, 0, 0, width, height);

                const data = uploadCanvas.toDataURL('image/jpeg', 0.85);

                fabric.Image.fromURL(data, function(img) {
                    // Determine scale to fit within the placeholder boundary
                const boxW = activeMaskZone.width * activeMaskZone.scaleX;
                const boxH = activeMaskZone.height * activeMaskZone.scaleY;
                
                const scale = Math.max(boxW / img.width, boxH / img.height);
                
                // Create an exact clipPath duplicating the mask's boundary
                const clipPath = new fabric.Rect({
                    left: activeMaskZone.left,
                    top: activeMaskZone.top,
                    width: activeMaskZone.width,
                    height: activeMaskZone.height,
                    scaleX: activeMaskZone.scaleX,
                    scaleY: activeMaskZone.scaleY,
                    angle: activeMaskZone.angle,
                    originX: activeMaskZone.originX,
                    originY: activeMaskZone.originY,
                    absolutePositioned: true,
                    rx: activeMaskZone._objects[0].rx,
                    ry: activeMaskZone._objects[0].ry
                });
                
                img.set({
                    left: activeMaskZone.left,
                    top: activeMaskZone.top,
                    scaleX: scale,
                    scaleY: scale,
                    angle: activeMaskZone.angle,
                    originX: activeMaskZone.originX,
                    originY: activeMaskZone.originY,
                    cornerColor: '#2563eb',
                    transparentCorners: false,
                    clipPath: clipPath
                });

                // Link image and mask together so we can restore the mask if image is deleted
                img._boundMaskZone = activeMaskZone;

                canvas.add(img);
                canvas.sendToBack(img); // Puts the image behind the frameImage overlay

                // Don't delete the mask shape! Hide the inner label and make it a transparent window border instead
                activeMaskZone.item(0).set({
                    fill: 'transparent',
                    stroke: 'transparent' // Make border fully transparent after image drops in
                });
                if (activeMaskZone.item(1)) {
                    activeMaskZone.item(1).set({ opacity: 0 }); // hide "Click to Upload" text
                }

                // Push the mask above the image but disable interaction so user clicks the image underneath to pan/resize
                activeMaskZone.set({
                    evented: false,
                    selectable: false
                });
                activeMaskZone._hasImage = true;
                canvas.bringToFront(activeMaskZone);
                
                activeMaskZone = null;
                
                canvas.setActiveObject(img);
                canvas.renderAll();
                checkPlaceholders();
            });
            };
            tempImg.src = f.target.result;
        };
        reader.readAsDataURL(file);
        
        // reset input
        e.target.value = '';
    }

    function checkPlaceholders() {
        let hasPlaceholders = false;
        canvas.getObjects().forEach(obj => {
            if (obj._isMaskPlaceholder && !obj._hasImage) {
                hasPlaceholders = true;
            }
        });

        const dlBtn = document.getElementById('downloadBtn');
        const cartBtn = document.getElementById('cartBtn');

        if (dlBtn && cartBtn) {
            if (hasPlaceholders) {
                dlBtn.disabled = true;
                cartBtn.disabled = true;
                dlBtn.classList.add('opacity-50', 'cursor-not-allowed', 'bg-gray-200');
                cartBtn.classList.add('opacity-50', 'cursor-not-allowed');
                dlBtn.classList.remove('bg-white');
            } else {
                dlBtn.disabled = false;
                cartBtn.disabled = false;
                dlBtn.classList.remove('opacity-50', 'cursor-not-allowed', 'bg-gray-200');
                dlBtn.classList.add('bg-white');
                cartBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }
    }

    function downloadDesign() {
        // Deselect objects to clear bounding boxes
        canvas.discardActiveObject();
        canvas.requestRenderAll();
        
        const dataURL = canvas.toDataURL({
            format: 'png',
            quality: 1,
            multiplier: 2 // High res export
        });
        
        const link = document.createElement('a');
        link.download = 'my-design-' + Date.now() + '.png';
        link.href = dataURL;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    async function openAddToCartModal() {
        const cartBtn = document.getElementById('cartBtn');
        const originalHtml = cartBtn.innerHTML;
        cartBtn.disabled = true;
        cartBtn.innerHTML = `<svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Please wait...`;

        canvas.discardActiveObject();
        canvas.requestRenderAll();

        const jsonState = canvas.toJSON();
        
        // Render high res preview
        // Add white background before capturing if canvas was transparent
        const originalBg = canvas.backgroundColor;
        canvas.backgroundColor = '#ffffff';
        canvas.requestRenderAll();

        const dataURL = canvas.toDataURL({
            format: 'jpeg',
            quality: 0.8,
            multiplier: 1
        });
        
        // Restore background
        canvas.backgroundColor = originalBg;
        canvas.requestRenderAll();

        // First save the design state to server
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // Show saving overlay or state
        const saveBtnText = "Saving Design...";
        // For simplicity we use standard fetch here
        
        try {
            const resp = await fetch('{{ route('api.user-designs.save') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    product_id: productData.id,
                    design_data: jsonState,
                    preview_image: dataURL
                })
            });

            let data;
            try {
                data = await resp.json();
            } catch (err) {
                if (resp.status === 413) {
                    throw new Error("Images are too large. Please try with smaller images or fewer images.");
                }
                throw new Error("Server returned an invalid response. Please try again.");
            }

            if(data.success) {
                // Dispatch event to Alpine JS modal
                window.dispatchEvent(new CustomEvent('open-cart-modal', {
                    detail: {
                        design_id: data.design_id,
                        preview_url: data.preview_url
                    }
                }));
            } else {
                alert('Failed to save design. ' + data.message);
            }
        } catch (e) {
            console.error(e);
            alert(e.message || 'Something went wrong. Please try again.');
        } finally {
            cartBtn.innerHTML = originalHtml;
            checkPlaceholders();
        }
    }

    // AlpineJS Modal Logic
    document.addEventListener('alpine:init', () => {
        Alpine.data('cartModal', () => ({
            isOpen: false,
            isSubmitting: false,
            designId: null,
            templatePreview: '',
            basePrice: parseFloat('{{ $product->active_price }}'),
            quantity: 1,
            selectedOptions: {},
            totalPrice: 0,
            
            init() {
                // Pre-select first option of each group if exists
                @foreach($product->optionGroups as $group)
                    @if($group->values->isNotEmpty())
                        this.selectedOptions['{{ $group->id }}'] = '{{ $group->values->first()->id }}';
                    @endif
                @endforeach
                this.calculateTotal();
            },

            openModal(detail) {
                this.designId = detail.design_id;
                this.templatePreview = detail.preview_url;
                this.isOpen = true;
                this.quantity = 1;
                this.calculateTotal();
                document.getElementById('cartModal').style.display = 'block';
            },

            closeModal() {
                this.isOpen = false;
                setTimeout(() => { document.getElementById('cartModal').style.display = 'none'; }, 300);
            },

            calculateTotal() {
                let currentPrice = this.basePrice;
                
                // Extremely simple DOM-based price extraction (mirroring the server side logic roughly)
                for (const groupId in this.selectedOptions) {
                    const valId = this.selectedOptions[groupId];
                    const inputOrOption = document.querySelector(`[value="${valId}"]`);
                    if(inputOrOption && inputOrOption.dataset.price) {
                        const mPrice = parseFloat(inputOrOption.dataset.price);
                        const mType = inputOrOption.dataset.type; // Assuming you added this to your original layout if applicable
                        
                        // We will assume 'fixed' for simplicity in client side display unless you expand it
                         currentPrice += mPrice;
                    }
                }

                this.totalPrice = Math.max(0, currentPrice * this.quantity).toFixed(2);
            },

            submitToCart() {
                this.isSubmitting = true;
                
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                fetch('{{ route('flow.cart.add') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        product_id: {{ $product->id }},
                        quantity: this.quantity,
                        selected_options: Object.values(this.selectedOptions), // convert dict to array of values
                        customization_data: {
                            type: 'noritsu_design',
                            design_id: this.designId,
                            preview_url: this.templatePreview
                        }
                    })
                })
                .then(res => res.json())
                .then(data => {
                    this.isSubmitting = false;
                    if(data.success) {
                        this.closeModal();
                        window.location.href = '{{ route('flow.cart.index') }}';
                    } else {
                        const errorMsg = data.message || (data.errors ? Object.values(data.errors).join('\n') : 'Error adding to cart.');
                        alert(errorMsg);
                    }
                })
                .catch(err => {
                    this.isSubmitting = false;
                    console.error(err);
                    alert('Network connection error: failed to add to cart. Please try again.');
                });
            }
        }))
    });
</script>
@endpush
