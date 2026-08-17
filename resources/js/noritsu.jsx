import React, { StrictMode, useState, useEffect, useRef, createContext, useContext, useCallback } from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter, Routes, Route, Link, useNavigate, useLocation } from 'react-router-dom';
import { Canvas as FabricCanvas, FabricImage, Rect as FabricRect, Circle as FabricCircle, Ellipse as FabricEllipse, Triangle as FabricTriangle, Polygon as FabricPolygon, Path as FabricPath, IText as FabricIText, Group as FabricGroup, Textbox as FabricTextbox } from 'fabric';

// ──── Responsive Hook ────
const useResponsive = () => {
    const [w, setW] = useState(window.innerWidth);
    useEffect(() => {
        const h = () => setW(window.innerWidth);
        window.addEventListener('resize', h);
        return () => window.removeEventListener('resize', h);
    }, []);
    return { isMobile: w < 640, isTablet: w >= 640 && w < 1024, isDesktop: w >= 1024, width: w };
};

// ──── Global State Context ────
const AppContext = createContext();
const useApp = () => useContext(AppContext);

const AppProvider = ({ children }) => {
    const [storeId, setStoreId] = useState(null);
    const [storeName, setStoreName] = useState('');
    const [selectedCard, setSelectedCard] = useState(null);
    const [userImages, setUserImages] = useState([]);
    const [selectedSize, setSelectedSize] = useState(null);
    const [editedCanvasData, setEditedCanvasData] = useState(null);
    const [user, setUser] = useState(null);
    const [orderId, setOrderId] = useState(null);

    const resetOrder = () => {
        setSelectedCard(null);
        setUserImages([]);
        setSelectedSize(null);
        setEditedCanvasData(null);
        setOrderId(null);
    };

    return (
        <AppContext.Provider value={{
            storeId, setStoreId, storeName, setStoreName,
            selectedCard, setSelectedCard, userImages, setUserImages,
            selectedSize, setSelectedSize, editedCanvasData, setEditedCanvasData,
            user, setUser, orderId, setOrderId, resetOrder
        }}>
            {children}
        </AppContext.Provider>
    );
};

// ──── Page Shell (responsive: mobile=full, desktop=centered card) ────
const PageShell = ({ children, wide }) => {
    const { isDesktop } = useResponsive();
    return (
        <div style={{
            maxWidth: wide ? '960px' : isDesktop ? '520px' : '100%',
            margin: '0 auto',
            background: '#fff',
            minHeight: isDesktop ? 'calc(100dvh - 40px)' : '100dvh',
            display: 'flex',
            flexDirection: 'column',
            borderRadius: isDesktop ? '16px' : '0',
            boxShadow: isDesktop ? '0 4px 40px rgba(0,0,0,0.08)' : 'none',
            marginTop: isDesktop ? '20px' : '0',
            marginBottom: isDesktop ? '20px' : '0',
            overflow: 'hidden',
        }}>
            {children}
        </div>
    );
};

// ──── Shared UI Components ────
const Header = ({ title, onBack, rightAction }) => {
    const { isDesktop } = useResponsive();
    return (
        <header style={{
            display: 'flex', alignItems: 'center', justifyContent: 'space-between',
            padding: isDesktop ? '16px 24px' : '14px 16px',
            borderBottom: '1px solid #f0f0f0',
            background: '#fff', position: 'sticky', top: 0, zIndex: 10
        }}>
            {onBack ? (
                <button onClick={onBack} style={{
                    background: 'none', border: 'none', fontSize: '15px', color: '#666',
                    cursor: 'pointer', padding: '8px 12px', borderRadius: '8px',
                    minWidth: '44px', minHeight: '44px', display: 'flex', alignItems: 'center'
                }}>
                    ← Back
                </button>
            ) : <span style={{ width: 70 }} />}
            <span style={{ fontWeight: 700, fontSize: isDesktop ? '18px' : '16px', color: '#111' }}>{title}</span>
            {rightAction || <span style={{ width: 70 }} />}
        </header>
    );
};

const BottomButton = ({ label, onClick, disabled }) => {
    const { isDesktop } = useResponsive();
    return (
        <div style={{
            padding: isDesktop ? '16px 24px' : '12px 16px',
            paddingBottom: `max(${isDesktop ? '16px' : '12px'}, env(safe-area-inset-bottom))`,
            borderTop: '1px solid #f0f0f0', background: '#fff',
            position: 'sticky', bottom: 0
        }}>
            <button onClick={onClick} disabled={disabled}
                style={{
                    width: '100%', padding: isDesktop ? '16px' : '14px',
                    borderRadius: '12px', border: 'none',
                    fontSize: isDesktop ? '16px' : '15px', fontWeight: 700,
                    cursor: disabled ? 'not-allowed' : 'pointer',
                    background: disabled ? '#ddd' : '#2563eb',
                    color: disabled ? '#999' : '#fff',
                    boxShadow: disabled ? 'none' : '0 4px 14px rgba(37,99,235,0.25)',
                    transition: 'all 0.2s',
                    minHeight: '48px'
                }}
            >
                {label}
            </button>
        </div>
    );
};

const StepIndicator = ({ current, total }) => (
    <div style={{ display: 'flex', gap: '4px', justifyContent: 'center', padding: '10px 20px' }}>
        {Array.from({ length: total }, (_, i) => (
            <div key={i} style={{
                height: '3px', flex: 1, borderRadius: '2px', maxWidth: '80px',
                background: i <= current ? '#2563eb' : '#e5e7eb',
                transition: 'background 0.3s'
            }} />
        ))}
    </div>
);


// ════════════════════════════════════════════════════
// 1. HOME (Scan QR)
// ════════════════════════════════════════════════════
const Home = () => {
    const navigate = useNavigate();
    const { setStoreId, setStoreName } = useApp();
    const { isDesktop } = useResponsive();

    const handleScanQR = (id) => {
        setStoreId(id);
        setStoreName(`Noritsu Store #${id}`);
        navigate(`/start?store_id=${id}`);
    };

    return (
        <div style={{
            display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center',
            minHeight: '100dvh',
            padding: isDesktop ? '48px' : '24px',
            background: 'linear-gradient(160deg, #f8faff 0%, #eef2ff 50%, #faf5ff 100%)'
        }}>
            <div style={{
                width: isDesktop ? '100px' : '80px', height: isDesktop ? '100px' : '80px',
                borderRadius: '24px', background: 'linear-gradient(135deg, #2563eb, #7c3aed)',
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                marginBottom: isDesktop ? '32px' : '24px',
                boxShadow: '0 12px 40px rgba(37,99,235,0.25)',
                animation: 'scaleIn 0.5s ease-out'
            }}>
                <svg width={isDesktop ? '48' : '40'} height={isDesktop ? '48' : '40'} fill="white" viewBox="0 0 24 24">
                    <path d="M3 3h6v6H3V3zm2 2v2h2V5H5zm8-2h6v6h-6V3zm2 2v2h2V5h-2zM3 13h6v8H3v-8zm2 2v4h2v-4H5zm8 6h2v-2h2v-2h-2v-2h2v2h2v-2h2v2h-2v2h2v2h-4v-2h-2v2z" />
                </svg>
            </div>
            <h1 style={{
                fontSize: isDesktop ? '36px' : '28px', fontWeight: 800, color: '#111',
                marginBottom: '8px', textAlign: 'center', animation: 'fadeInUp 0.5s ease-out 0.1s both'
            }}>Print Made Easy</h1>
            <p style={{
                color: '#666', textAlign: 'center', maxWidth: '380px',
                marginBottom: isDesktop ? '40px' : '32px', lineHeight: '1.6',
                fontSize: isDesktop ? '16px' : '14px',
                animation: 'fadeInUp 0.5s ease-out 0.2s both'
            }}>
                Scan a QR code at your local store to start printing photos and cards instantly.
            </p>

            <div style={{
                background: '#fffbeb', border: '1px solid #fde68a', padding: '16px 20px',
                borderRadius: '12px', marginBottom: isDesktop ? '40px' : '32px',
                maxWidth: '380px', width: '100%', animation: 'fadeInUp 0.5s ease-out 0.3s both'
            }}>
                <p style={{ color: '#92400e', fontSize: '13px', textAlign: 'center' }}>
                    ⚠️ No store code detected. Please scan the QR code at a participating location.
                </p>
            </div>

            {/* Dev Mode */}
            <div style={{
                width: '100%', maxWidth: '380px',
                borderTop: '1px solid #e5e7eb', paddingTop: '24px',
                animation: 'fadeInUp 0.5s ease-out 0.4s both'
            }}>
                <p style={{ textAlign: 'center', fontSize: '10px', color: '#aaa', textTransform: 'uppercase', letterSpacing: '2px', marginBottom: '12px' }}>Developer Mode</p>
                <div style={{
                    background: '#fff', padding: isDesktop ? '20px' : '16px',
                    borderRadius: '14px', boxShadow: '0 2px 12px rgba(0,0,0,0.06)',
                    display: 'flex', alignItems: 'center', gap: '16px'
                }}>
                    <div style={{ flex: 1 }}>
                        <p style={{ fontWeight: 700, fontSize: '14px', color: '#111' }}>Noritsu Store #1</p>
                        <p style={{ fontSize: '12px', color: '#888', marginBottom: '10px' }}>Simulate QR scan</p>
                        <button onClick={() => handleScanQR(1)} style={{
                            fontSize: '13px', background: '#2563eb', color: '#fff', border: 'none',
                            padding: '8px 20px', borderRadius: '20px', cursor: 'pointer', fontWeight: 600,
                            minHeight: '36px', transition: 'background 0.15s'
                        }}>
                            Scan QR Code →
                        </button>
                    </div>
                    <div style={{
                        width: isDesktop ? '64px' : '56px', height: isDesktop ? '64px' : '56px',
                        background: '#f9fafb', border: '1px solid #e5e7eb', borderRadius: '10px',
                        display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0
                    }}>
                        <svg width="32" height="32" fill="#333" viewBox="0 0 24 24"><path d="M3 3h6v6H3V3zm2 2v2h2V5H5zm8-2h6v6h-6V3zm2 2v2h2V5h-2zM3 13h6v8H3v-8zm2 2v4h2v-4H5z" /></svg>
                    </div>
                </div>
            </div>
        </div>
    );
};


// ════════════════════════════════════════════════════
// 2. START ORDER
// ════════════════════════════════════════════════════
const StartOrder = () => {
    const navigate = useNavigate();
    const { storeId, setStoreId, setStoreName } = useApp();
    const { isMobile, isDesktop } = useResponsive();

    useEffect(() => {
        const params = new URLSearchParams(window.location.search);
        const sid = params.get('store_id');
        if (sid && !storeId) {
            setStoreId(sid);
            setStoreName(`Noritsu Store #${sid}`);
        }
    }, []);

    return (
        <PageShell>
            <Header title="Noritsu" rightAction={
                storeId && <span style={{
                    fontSize: '12px', color: '#16a34a', background: '#f0fdf4',
                    padding: '6px 12px', borderRadius: '8px', fontWeight: 600
                }}>Store #{storeId}</span>
            } />

            <main style={{
                flex: 1,
                padding: isDesktop ? '48px 40px' : isMobile ? '24px 20px' : '32px 24px',
                display: 'flex', flexDirection: 'column',
                alignItems: 'center', justifyContent: 'center', gap: isDesktop ? '32px' : '24px'
            }}>
                {/* Hero visual */}
                <div style={{
                    width: '100%', maxWidth: '400px',
                    aspectRatio: isMobile ? '16/10' : '16/9',
                    background: 'linear-gradient(135deg, #eef2ff 0%, #faf5ff 50%, #fdf2f8 100%)',
                    borderRadius: isDesktop ? '20px' : '16px',
                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                    border: '2px dashed #d4d9e8',
                    animation: 'fadeInUp 0.5s ease-out'
                }}>
                    <div style={{ textAlign: 'center', padding: '16px' }}>
                        <svg style={{ width: isDesktop ? '56px' : '44px', height: isDesktop ? '56px' : '44px', color: '#c4b5fd', margin: '0 auto 10px' }} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span style={{ fontSize: isDesktop ? '16px' : '14px', color: '#8b8fa3', fontWeight: 500 }}>Fast & Easy Printing</span>
                    </div>
                </div>

                {/* Title */}
                <div style={{ textAlign: 'center', animation: 'fadeInUp 0.5s ease-out 0.1s both' }}>
                    <h2 style={{
                        fontSize: isDesktop ? '30px' : '24px',
                        fontWeight: 800, color: '#111', marginBottom: '8px'
                    }}>Start Your Order</h2>
                    <p style={{
                        color: '#888',
                        fontSize: isDesktop ? '16px' : '14px',
                        maxWidth: '340px', lineHeight: '1.5'
                    }}>Choose a card, add your photos, and customize it. Pick up in store or get it delivered.</p>
                </div>

                {/* Action Buttons */}
                <div style={{
                    width: '100%', maxWidth: '380px',
                    display: 'flex', flexDirection: 'column', gap: '12px',
                    animation: 'fadeInUp 0.5s ease-out 0.2s both'
                }}>
                    <button onClick={() => navigate('/cards')} style={{
                        width: '100%',
                        padding: isDesktop ? '18px' : '16px',
                        borderRadius: '14px', border: 'none',
                        fontSize: isDesktop ? '17px' : '16px', fontWeight: 700,
                        cursor: 'pointer', background: '#2563eb', color: '#fff',
                        boxShadow: '0 6px 20px rgba(37,99,235,0.3)',
                        transition: 'transform 0.1s, box-shadow 0.2s',
                        minHeight: '52px'
                    }}>
                        🎨 Browse Cards
                    </button>
                    <button onClick={() => navigate('/own-design')} style={{
                        width: '100%',
                        padding: isDesktop ? '18px' : '16px',
                        borderRadius: '14px',
                        border: '2px solid #e5e7eb',
                        fontSize: isDesktop ? '17px' : '16px', fontWeight: 600,
                        cursor: 'pointer', background: '#fff', color: '#333',
                        transition: 'border-color 0.2s, background 0.2s',
                        minHeight: '52px'
                    }}>
                        📤 Upload Own Design
                    </button>
                </div>

                {/* Features chips */}
                <div style={{
                    display: 'flex', flexWrap: 'wrap', gap: '8px',
                    justifyContent: 'center', maxWidth: '380px',
                    animation: 'fadeInUp 0.5s ease-out 0.3s both'
                }}>
                    {['📸 Photo Cards', '🎁 Gift Cards', '💌 Postcards', '🖼️ Posters'].map(f => (
                        <span key={f} style={{
                            fontSize: '12px', color: '#666', background: '#f3f4f6',
                            padding: '6px 14px', borderRadius: '20px', fontWeight: 500
                        }}>{f}</span>
                    ))}
                </div>
            </main>

            <footer style={{
                padding: isDesktop ? '20px' : '16px',
                paddingBottom: `max(${isDesktop ? '20px' : '16px'}, env(safe-area-inset-bottom))`,
                textAlign: 'center', fontSize: '11px', color: '#ccc'
            }}>Powered by Noritsu</footer>
        </PageShell>
    );
};


// ════════════════════════════════════════════════════
// 3. CARD LIST (Products/Templates from backend)
// ════════════════════════════════════════════════════
const CardList = () => {
    const navigate = useNavigate();
    const { setSelectedCard } = useApp();
    const { isMobile, isDesktop } = useResponsive();
    const [templates, setTemplates] = useState([]);
    const [loading, setLoading] = useState(true);
    const [activeCategory, setActiveCategory] = useState('all');

    useEffect(() => {
        fetch('/custom/public/api/noritsu/v1/templates')
            .then(res => res.json())
            .then(data => { setTemplates(data); setLoading(false); })
            .catch(() => setLoading(false));
    }, []);

    const categories = ['all', ...new Set(templates.map(t => t.category || 'Other'))];
    const filtered = activeCategory === 'all' ? templates : templates.filter(t => (t.category || 'Other') === activeCategory);

    const handleSelect = (card) => {
        setSelectedCard(card);
        navigate('/editor');
    };

    const cols = isMobile ? '1fr 1fr' : isDesktop ? '1fr 1fr 1fr' : '1fr 1fr 1fr';

    return (
        <PageShell wide={isDesktop}>
            <Header title="Choose a Card" onBack={() => navigate(-1)} />
            <StepIndicator current={0} total={6} />

            {/* Category Tabs */}
            <div style={{
                display: 'flex', gap: '8px',
                padding: isDesktop ? '8px 24px' : '8px 16px',
                overflowX: 'auto'
            }}>
                {categories.map(cat => (
                    <button key={cat} onClick={() => setActiveCategory(cat)} style={{
                        padding: '8px 18px', borderRadius: '20px', border: 'none',
                        fontSize: '13px', fontWeight: 600, cursor: 'pointer',
                        whiteSpace: 'nowrap', transition: 'all 0.2s',
                        background: activeCategory === cat ? '#2563eb' : '#f3f4f6',
                        color: activeCategory === cat ? '#fff' : '#555',
                        minHeight: '36px'
                    }}>
                        {cat.charAt(0).toUpperCase() + cat.slice(1)}
                    </button>
                ))}
            </div>

            <main style={{ flex: 1, padding: isDesktop ? '16px 24px' : '12px 16px' }}>
                {loading ? (
                    <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '200px' }}>
                        <div style={{ width: '32px', height: '32px', border: '3px solid #e5e7eb', borderTopColor: '#2563eb', borderRadius: '50%', animation: 'spin 0.8s linear infinite' }} />
                    </div>
                ) : (
                    <div style={{ display: 'grid', gridTemplateColumns: cols, gap: isDesktop ? '16px' : '12px' }}>
                        {filtered.map(tpl => (
                            <div key={tpl.id} onClick={() => handleSelect(tpl)}
                                className="n-card-hover"
                                style={{
                                    border: '1px solid #e5e7eb', borderRadius: '12px', overflow: 'hidden',
                                    cursor: 'pointer', transition: 'box-shadow 0.2s, transform 0.15s',
                                    boxShadow: '0 1px 4px rgba(0,0,0,0.06)', background: '#fff'
                                }}>
                                <div style={{ aspectRatio: '3/4', background: '#f9fafb' }}>
                                    <img src={tpl.image} alt={tpl.name} style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
                                </div>
                                <div style={{ padding: isDesktop ? '12px 14px' : '8px 10px' }}>
                                    <h3 style={{ fontWeight: 700, fontSize: isDesktop ? '14px' : '13px', color: '#111', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{tpl.name}</h3>
                                    <p style={{ fontSize: '12px', color: '#2563eb', fontWeight: 600, marginTop: '2px' }}>${tpl.price}</p>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </main>
        </PageShell>
    );
};


// ════════════════════════════════════════════════════
// 4. OWN DESIGN
// ════════════════════════════════════════════════════
const OwnDesign = () => {
    const navigate = useNavigate();
    const { setSelectedCard, setUserImages } = useApp();
    const { isMobile, isDesktop } = useResponsive();
    const [images, setImages] = useState([]);

    const handleFileChange = (e) => {
        if (e.target.files) {
            const newImages = Array.from(e.target.files).map(file => ({
                file, preview: URL.createObjectURL(file)
            }));
            setImages(prev => [...prev, ...newImages]);
        }
    };

    const handleContinue = () => {
        setSelectedCard({ id: 'own', name: 'Own Design', price: 0, image: null, frame_image: null });
        setUserImages(images);
        navigate('/select-size');
    };

    const cols = isMobile ? '1fr 1fr' : '1fr 1fr 1fr';

    return (
        <PageShell>
            <Header title="Own Design" onBack={() => navigate(-1)} />
            <StepIndicator current={1} total={6} />

            <main style={{ flex: 1, padding: isDesktop ? '24px' : '16px' }}>
                {images.length === 0 ? (
                    <label style={{
                        display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center',
                        height: isDesktop ? '300px' : '240px',
                        border: '2px dashed #d1d5db', borderRadius: '16px',
                        background: '#f9fafb', cursor: 'pointer', transition: 'border-color 0.2s'
                    }}>
                        <svg style={{ width: '48px', height: '48px', color: '#9ca3af', marginBottom: '12px' }} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                        </svg>
                        <span style={{ color: '#555', fontWeight: 600, fontSize: '15px' }}>Click to upload your design</span>
                        <span style={{ color: '#aaa', fontSize: '13px', marginTop: '4px' }}>JPG, PNG supported</span>
                        <input type="file" multiple accept="image/*" style={{ display: 'none' }} onChange={handleFileChange} />
                    </label>
                ) : (
                    <div style={{ display: 'grid', gridTemplateColumns: cols, gap: '12px' }}>
                        {images.map((img, idx) => (
                            <div key={idx} style={{ position: 'relative', aspectRatio: '1', borderRadius: '12px', overflow: 'hidden', background: '#f3f4f6' }}>
                                <img src={img.preview} alt="" style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
                                <button onClick={() => setImages(images.filter((_, i) => i !== idx))} style={{
                                    position: 'absolute', top: '6px', right: '6px', width: '28px', height: '28px',
                                    borderRadius: '50%', background: 'rgba(0,0,0,0.5)', color: '#fff',
                                    border: 'none', fontSize: '16px', cursor: 'pointer',
                                    display: 'flex', alignItems: 'center', justifyContent: 'center'
                                }}>×</button>
                            </div>
                        ))}
                        <label style={{
                            display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center',
                            aspectRatio: '1', border: '2px dashed #d1d5db', borderRadius: '12px',
                            cursor: 'pointer', background: '#f9fafb'
                        }}>
                            <span style={{ fontSize: '28px', color: '#9ca3af' }}>+</span>
                            <span style={{ fontSize: '11px', color: '#aaa' }}>Add More</span>
                            <input type="file" multiple accept="image/*" style={{ display: 'none' }} onChange={handleFileChange} />
                        </label>
                    </div>
                )}
            </main>
            <BottomButton label={`Continue (${images.length} files)`} onClick={handleContinue} disabled={images.length === 0} />
        </PageShell>
    );
};


// ════════════════════════════════════════════════════
// 5. EDIT DESIGN — Mask-aware editor
// ════════════════════════════════════════════════════

// Helper: polygon points
const polygonPts = (sides, r) => {
    const pts = [];
    for (let i = 0; i < sides; i++) {
        const a = (Math.PI * 2 * i) / sides - Math.PI / 2;
        pts.push({ x: r + r * Math.cos(a), y: r + r * Math.sin(a) });
    }
    return pts;
};
const starPts = (spikes, outer, inner) => {
    const pts = [];
    for (let i = 0; i < spikes * 2; i++) {
        const r = i % 2 === 0 ? outer : inner;
        const a = (Math.PI * i) / spikes - Math.PI / 2;
        pts.push({ x: outer + r * Math.cos(a), y: outer + r * Math.sin(a) });
    }
    return pts;
};
const heartSvg = () => 'M 50 30 C 50 25 45 10 30 10 C 10 10 0 25 0 40 C 0 60 25 80 50 100 C 75 80 100 60 100 40 C 100 25 90 10 70 10 C 55 10 50 25 50 30 Z';

// Create a clip-path shape for fabric (unscaled, same shape as mask)
const makeClipShape = (maskDef, w, h) => {
    const t = maskDef.type || 'square';
    switch (t) {
        case 'circle': {
            const r = maskDef.radius || Math.min(w, h) / 2;
            return new FabricCircle({ radius: r, originX: 'center', originY: 'center' });
        }
        case 'ellipse':
        case 'oval': {
            const rx = maskDef.rx || w / 2;
            const ry = maskDef.ry || h / 2;
            return new FabricEllipse({ rx, ry, originX: 'center', originY: 'center' });
        }
        case 'triangle':
            return new FabricTriangle({ width: w, height: h, originX: 'center', originY: 'center' });
        case 'pentagon':
            return new FabricPolygon(polygonPts(5, Math.min(w, h) / 2), { originX: 'center', originY: 'center' });
        case 'hexagon':
            return new FabricPolygon(polygonPts(6, Math.min(w, h) / 2), { originX: 'center', originY: 'center' });
        case 'star':
            return new FabricPolygon(starPts(5, Math.min(w, h) / 2, Math.min(w, h) / 4), { originX: 'center', originY: 'center' });
        case 'heart':
            return new FabricPath(heartSvg(), { originX: 'center', originY: 'center', scaleX: w / 100, scaleY: h / 100 });
        default: // square, rectangle
            return new FabricRect({ width: w, height: h, originX: 'center', originY: 'center' });
    }
};

// Build visible mask placeholder (red bg + "Upload Image" text)
const makeMaskPlaceholder = (maskDef, scale) => {
    const t = maskDef.type || 'square';
    const w = (maskDef.width || maskDef.radius * 2 || maskDef.rx * 2 || 100);
    const h = (maskDef.height || maskDef.radius * 2 || maskDef.ry * 2 || 100);

    let shape;
    const baseProps = {
        fill: 'rgba(220, 38, 38, 0.25)',
        stroke: 'rgba(220, 38, 38, 0.6)',
        strokeWidth: 2,
        originX: 'center', originY: 'center'
    };

    switch (t) {
        case 'circle': {
            const r = maskDef.radius || 50;
            shape = new FabricCircle({ ...baseProps, radius: r });
            break;
        }
        case 'ellipse':
        case 'oval': {
            shape = new FabricEllipse({ ...baseProps, rx: maskDef.rx || 60, ry: maskDef.ry || 40 });
            break;
        }
        case 'triangle':
            shape = new FabricTriangle({ ...baseProps, width: w, height: h });
            break;
        case 'pentagon':
            shape = new FabricPolygon(polygonPts(5, Math.min(w, h) / 2), baseProps);
            break;
        case 'hexagon':
            shape = new FabricPolygon(polygonPts(6, Math.min(w, h) / 2), baseProps);
            break;
        case 'star':
            shape = new FabricPolygon(starPts(5, Math.min(w, h) / 2, Math.min(w, h) / 4), baseProps);
            break;
        case 'heart':
            shape = new FabricPath(heartSvg(), { ...baseProps, scaleX: w / 100, scaleY: h / 100 });
            break;
        default:
            shape = new FabricRect({ ...baseProps, width: w, height: h });
    }

    // Label
    const label = new FabricIText('📷 Upload Image', {
        fontSize: Math.max(11, Math.min(16, w * 0.14)),
        fontFamily: 'Arial', fill: 'rgba(180, 30, 30, 0.85)',
        fontWeight: '600', originX: 'center', originY: 'center',
        editable: false, selectable: false
    });

    const group = new FabricGroup([shape, label], {
        left: maskDef.left * scale,
        top: maskDef.top * scale,
        scaleX: (maskDef.scaleX || 1) * scale,
        scaleY: (maskDef.scaleY || 1) * scale,
        angle: maskDef.angle || 0,
        selectable: true, hasControls: false, hasBorders: true,
        borderColor: '#dc2626', hoverCursor: 'pointer',
        lockMovementX: true, lockMovementY: true,
        lockScalingX: true, lockScalingY: true, lockRotation: true,
    });
    group._maskDef = maskDef;
    group._maskType = 'placeholder';
    group._maskShapeW = w;
    group._maskShapeH = h;

    return group;
};


const EditDesign = () => {
    const navigate = useNavigate();
    const { selectedCard, userImages, setUserImages, setEditedCanvasData } = useApp();
    const { isMobile, isDesktop } = useResponsive();
    const canvasRef = useRef(null);
    const fabricRef = useRef(null);
    const containerRef = useRef(null);
    const fileInputRef = useRef(null);
    const activeMaskRef = useRef(null); // track which mask placeholder was clicked
    const scaleRef = useRef(1);

    // Sidebar state
    const [selectedObj, setSelectedObj] = useState(null);
    const [textProps, setTextProps] = useState({ text: '', fontSize: 24, fontFamily: 'Arial', fill: '#000000' });
    const [showTextPanel, setShowTextPanel] = useState(false);
    const [addTextMode, setAddTextMode] = useState(false);
    const [newText, setNewText] = useState('Hello');
    const [newFontSize, setNewFontSize] = useState(24);
    const [newFontFamily, setNewFontFamily] = useState('Arial');
    const [newFontColor, setNewFontColor] = useState('#000000');

    const fonts = ['Arial', 'Georgia', 'Times New Roman', 'Verdana', 'Courier New', 'Impact', 'Comic Sans MS', 'Trebuchet MS'];

    const handleSelection = (e) => {
        const obj = e.selected?.[0];
        if (!obj) { setSelectedObj(null); setShowTextPanel(false); return; }
        setSelectedObj(obj);
        if (obj._isEditorText) {
            setShowTextPanel(true);
            setTextProps({
                text: obj.text || '',
                fontSize: obj.fontSize || 24,
                fontFamily: obj.fontFamily || 'Arial',
                fill: obj.fill || '#000000'
            });
        } else {
            setShowTextPanel(false);
        }
    };

    // ── Init canvas + Load card content (combined to avoid StrictMode race condition)
    useEffect(() => {
        if (!canvasRef.current || !selectedCard) return;
        let cancelled = false;

        // Clean up any leftover Fabric.js state from previous mount (React StrictMode)
        const el = canvasRef.current;
        el.removeAttribute('data-fabric');
        el.classList.remove('lower-canvas');
        // If Fabric wrapped the canvas in a container div, unwrap it
        const wrapper = el.parentNode;
        if (wrapper && wrapper.getAttribute?.('data-fabric') === 'wrapper') {
            const parent = wrapper.parentNode;
            if (parent) {
                parent.insertBefore(el, wrapper);
                parent.removeChild(wrapper);
            }
        }
        // Remove any leftover upper-canvas siblings
        el.parentNode?.querySelectorAll('[data-fabric="top"]').forEach(c => c.remove());

        const canvas = new FabricCanvas(el, {
            width: 100, height: 100, backgroundColor: '#ffffff', selection: false,
            preserveObjectStacking: true
        });
        fabricRef.current = canvas;

        // Selection events
        canvas.on('selection:created', (e) => handleSelection(e));
        canvas.on('selection:updated', (e) => handleSelection(e));
        canvas.on('selection:cleared', () => { setSelectedObj(null); setShowTextPanel(false); });

        // Click on mask placeholder → trigger file upload
        canvas.on('mouse:down', (opt) => {
            const target = opt.target;
            if (target && target._maskType === 'placeholder') {
                activeMaskRef.current = target;
                fileInputRef.current?.click();
            }
        });

        // ── Load content
        const loadContent = async () => {
            try {
                const frameUrl = selectedCard.frame_image || selectedCard.image;
                const containerW = containerRef.current?.clientWidth || 400;
                const maxW = Math.min(containerW - 32, isDesktop ? 560 : 400);

                // Parse mask_data
                const maskData = selectedCard.mask_data;
                const mData = maskData
                    ? (typeof maskData === 'string' ? JSON.parse(maskData) : maskData)
                    : null;

                let adminCW = 600;
                let adminCH = 600;
                let ratio = 1;

                if (frameUrl) {
                    try {
                        const frameImg = await FabricImage.fromURL(frameUrl, { crossOrigin: 'anonymous' });

                        if (cancelled) return;

                        const imgW = frameImg.width;
                        const imgH = frameImg.height;

                        adminCW = (mData && mData.canvasWidth) || imgW;
                        adminCH = (mData && mData.canvasHeight) || imgH;
                        const adminRatio = adminCW / adminCH;

                        const canvasW = 600;
                        const canvasH = Math.round(canvasW / adminRatio);
                        canvas.setDimensions({ width: canvasW, height: canvasH });

                        ratio = canvasW / adminCW;
                        scaleRef.current = ratio;

                        frameImg.set({
                            left: 0, top: 0,
                            scaleX: canvasW / imgW,
                            scaleY: canvasH / imgH,
                            selectable: false, evented: false,
                            hasControls: false, hasBorders: false
                        });

                        frameImg._isFrame = true;
                        canvas.add(frameImg);

                        console.log('[Editor] Frame:', imgW, 'x', imgH, '→ canvas:', canvasW, 'x', canvasH, '→ ratio:', ratio.toFixed(4));
                        // ── Render masks on top of frame
                        if (mData && mData.masks && mData.masks.length) {
                            console.log('[Editor] Admin canvas:', adminCW, 'x', adminCH, '→ ratio:', ratio.toFixed(4));

                            mData.masks.forEach((m, i) => {
                                // Map exactly using original dimensions, applying the canvas responsive ratio to scale/position
                                const fLeft = m.left * ratio;
                                const fTop = m.top * ratio;
                                const fScaleX = (m.scaleX || 1) * ratio;
                                const fScaleY = (m.scaleY || 1) * ratio;

                                console.log('[Editor] Mask', i, m.type, ':', fLeft.toFixed(1), fTop.toFixed(1), 'scale:', fScaleX.toFixed(2));

                                if (m.type === 'text') {
                                    const txt = new FabricIText(m.text || 'Text', {
                                        left: fLeft, top: fTop,
                                        scaleX: fScaleX, scaleY: fScaleY,
                                        angle: m.angle || 0,
                                        fontSize: m.fontSize || 24,
                                        fontFamily: m.fontFamily || 'Arial',
                                        fill: m.fill || '#000000',
                                        fontWeight: m.fontWeight || 'normal',
                                        fontStyle: m.fontStyle || 'normal',
                                        editable: true, selectable: true,
                                        cornerColor: '#2563eb', cornerStrokeColor: '#2563eb',
                                        borderColor: '#2563eb', transparentCorners: false,
                                        cornerSize: 10
                                    });
                                    txt._isEditorText = true;
                                    txt._maskDef = m;
                                    canvas.add(txt);
                                } else {
                                    // For shapes, use exact dimensions from save data, and scale via scaleX/scaleY
                                    const rawW = m.width || (m.radius ? m.radius * 2 : 100);
                                    const rawH = m.height || (m.ry ? m.ry * 2 : 100);

                                    const shape = new FabricRect({
                                        width: rawW, height: rawH,
                                        fill: 'rgba(220, 38, 38, 0.3)',
                                        stroke: 'rgba(220, 38, 38, 0.7)',
                                        strokeWidth: 4 / fScaleX, // Keep stroke width visually consistent
                                        rx: 4 / fScaleX, ry: 4 / fScaleY
                                    });

                                    // Group label shouldn't stretch bizarrely, so we reverse the scaling inside the label
                                    const labelText = new FabricIText('📷 Upload Image', {
                                        fontSize: Math.max(12, rawW * 0.1),
                                        fontFamily: 'Arial',
                                        fill: 'rgba(180, 30, 30, 0.9)',
                                        fontWeight: '600',
                                        originX: 'center', originY: 'center',
                                        left: rawW / 2, top: rawH / 2,
                                        scaleX: 1 / fScaleX, // counter-scale the group stretch so text looks normal
                                        scaleY: 1 / fScaleY,
                                        editable: false, selectable: false
                                    });

                                    const group = new FabricGroup([shape, labelText], {
                                        left: fLeft, top: fTop,
                                        scaleX: fScaleX, scaleY: fScaleY,
                                        angle: m.angle || 0,
                                        selectable: true, hasControls: false, hasBorders: true,
                                        borderColor: '#dc2626', hoverCursor: 'pointer',
                                        lockMovementX: true, lockMovementY: true,
                                        lockScalingX: true, lockScalingY: true, lockRotation: true,
                                    });
                                    group._maskDef = m;
                                    group._maskType = 'placeholder';
                                    group._maskShapeW = rawW * fScaleX;
                                    group._maskShapeH = rawH * fScaleY;
                                    canvas.add(group);
                                }
                            });
                        }
                    } catch (err) {
                        console.error('Failed to load frame', err);
                    }
                } else {
                    canvas.setDimensions({ width: maxW, height: maxW });
                    scaleRef.current = 1;
                }

                canvas.renderAll();
                console.log('[Editor] Total objects:', canvas.getObjects().length);
            } catch (err) {
                console.error('[Editor] Load error:', err);
                if (!cancelled) {
                    try {
                        canvas.setDimensions({ width: 600, height: 600 });
                        canvas.renderAll();
                    } catch (e) { /* canvas disposed */ }
                }
            }
        };
        loadContent();

        return () => {
            cancelled = true;
            canvas.dispose();
            fabricRef.current = null;
        };
    }, [selectedCard, isDesktop]);

    // Render mask placeholders and text masks
    // mData is already parsed, ratio = frontendCanvasWidth / adminCanvasWidth
    const renderMasks = (canvas, mData, ratio) => {
        const masks = mData.masks || [];
        if (!masks.length) return;

        masks.forEach((m) => {
            if (m.type === 'text') {
                // Text mask: show as editable text
                const txt = new FabricIText(m.text || 'Text', {
                    left: m.left * ratio,
                    top: m.top * ratio,
                    scaleX: (m.scaleX || 1) * ratio,
                    scaleY: (m.scaleY || 1) * ratio,
                    angle: m.angle || 0,
                    fontSize: m.fontSize || 24,
                    fontFamily: m.fontFamily || 'Arial',
                    fill: m.fill || '#000000',
                    fontWeight: m.fontWeight || 'normal',
                    fontStyle: m.fontStyle || 'normal',
                    editable: true, selectable: true,
                    cornerColor: '#2563eb', cornerStrokeColor: '#2563eb',
                    borderColor: '#2563eb', transparentCorners: false, cornerSize: 10
                });
                txt._isEditorText = true;
                txt._maskDef = m;
                canvas.add(txt);
            } else {
                // Shape mask: show red placeholder
                const placeholder = makeMaskPlaceholder(m, ratio);
                canvas.add(placeholder);
            }
        });
    };

    // ── Handle file selected for a mask
    const handleMaskFileChange = async (e) => {
        const file = e.target.files?.[0];
        const placeholder = activeMaskRef.current;
        const canvas = fabricRef.current;
        if (!file || !placeholder || !canvas) return;

        const url = URL.createObjectURL(file);

        try {
            const img = await FabricImage.fromURL(url);
            const mDef = placeholder._maskDef;
            const mW = placeholder._maskShapeW;
            const mH = placeholder._maskShapeH;
            const ratio = scaleRef.current;

            // The mask's actual displayed size
            const displayW = mW * (mDef.scaleX || 1) * ratio;
            const displayH = mH * (mDef.scaleY || 1) * ratio;

            // Scale image to cover the mask area
            const covS = Math.max(displayW / img.width, displayH / img.height);

            // Create clip path matching the mask shape (in local coords, unscaled)
            const clip = makeClipShape(mDef, displayW / covS, displayH / covS);

            // Position the image at the mask center
            const centerX = mDef.left * ratio + displayW / 2;
            const centerY = mDef.top * ratio + displayH / 2;

            img.set({
                left: centerX,
                top: centerY,
                scaleX: covS, scaleY: covS,
                angle: mDef.angle || 0,
                clipPath: clip,
                originX: 'center', originY: 'center',
                selectable: true, hasControls: true, hasBorders: true,
                cornerColor: '#fff', cornerStrokeColor: '#2563eb',
                borderColor: '#2563eb', transparentCorners: false, cornerSize: 12,
            });

            img._isMaskImage = true;
            img._maskDef = mDef;

            // Remove the placeholder
            canvas.remove(placeholder);
            canvas.add(img);


            canvas.setActiveObject(img);
            canvas.renderAll();
        } catch (err) {
            console.error("Mask image load error:", err);
        }

        // Reset input
        e.target.value = '';
        activeMaskRef.current = null;
    };

    // ── Add text
    const handleAddText = () => {
        const canvas = fabricRef.current;
        if (!canvas) return;
        const txt = new FabricIText(newText || 'Text', {
            left: canvas.width / 2, top: canvas.height / 2,
            originX: 'center', originY: 'center',
            fontSize: parseInt(newFontSize) || 24,
            fontFamily: newFontFamily,
            fill: newFontColor,
            fontWeight: 'normal', fontStyle: 'normal',
            editable: true, selectable: true,
            cornerColor: '#2563eb', cornerStrokeColor: '#2563eb',
            borderColor: '#2563eb', transparentCorners: false, cornerSize: 10
        });
        txt._isEditorText = true;
        canvas.add(txt);
        canvas.setActiveObject(txt);
        canvas.renderAll();
        setAddTextMode(false);
    };

    // ── Update selected text properties
    const updateTextProp = (key, val) => {
        const canvas = fabricRef.current;
        const obj = canvas?.getActiveObject();
        if (!obj || !obj._isEditorText) return;
        obj.set(key, val);
        canvas.renderAll();
        setTextProps(prev => ({ ...prev, [key]: val }));
    };

    // ── Delete selected
    const handleDelete = () => {
        const canvas = fabricRef.current;
        const obj = canvas?.getActiveObject();
        if (!obj) return;
        canvas.remove(obj);
        canvas.discardActiveObject();
        canvas.renderAll();
        setSelectedObj(null);
        setShowTextPanel(false);
    };

    // ── Save (serialize canvas state)
    const handleSave = () => {
        const canvas = fabricRef.current;
        if (!canvas) return;
        const json = canvas.toJSON(['_isEditorText', '_isMaskImage', '_maskDef', '_maskType']);
        setEditedCanvasData(json);
        alert('Design saved successfully!');
    };

    // ── Next
    const handleNext = () => {
        const canvas = fabricRef.current;
        if (canvas) {
            const json = canvas.toJSON(['_isEditorText', '_isMaskImage', '_maskDef', '_maskType']);
            setEditedCanvasData(json);
        }
        setUserImages(userImages);
        navigate('/select-size');
    };

    if (!selectedCard) return (
        <PageShell>
            <div style={{ padding: '60px 24px', textAlign: 'center' }}>
                <p style={{ color: '#888', marginBottom: '12px' }}>No card selected.</p>
                <Link to="/cards" style={{ color: '#2563eb', fontWeight: 600 }}>Go back to cards</Link>
            </div>
        </PageShell>
    );

    const sidebarW = isDesktop ? 260 : 0;
    const showSidebarBelow = !isDesktop;

    return (
        <PageShell wide={true}>
            <Header title="Edit Design" onBack={() => navigate(-1)} rightAction={
                <div style={{ display: 'flex', gap: '8px', alignItems: 'center' }}>
                    <button onClick={handleSave} style={{
                        background: '#f0fdf4', border: '1px solid #bbf7d0', color: '#16a34a',
                        fontWeight: 600, fontSize: '13px', cursor: 'pointer',
                        padding: '6px 14px', borderRadius: '8px', minHeight: '36px'
                    }}>💾 Save</button>
                    <button onClick={handleNext} style={{
                        background: '#2563eb', border: 'none', color: '#fff',
                        fontWeight: 700, fontSize: '14px', cursor: 'pointer',
                        padding: '8px 16px', borderRadius: '8px', minHeight: '36px'
                    }}>Next →</button>
                </div>
            } />
            <StepIndicator current={1} total={6} />

            {/* Hidden file input for mask uploads */}
            <input ref={fileInputRef} type="file" accept="image/*" style={{ display: 'none' }}
                onChange={handleMaskFileChange} />

            <div style={{
                display: 'flex',
                flexDirection: isDesktop ? 'row' : 'column',
                flex: 1, overflow: 'hidden'
            }}>
                {/* Canvas area */}
                <main ref={containerRef} style={{
                    flex: 1, padding: isDesktop ? '20px' : '12px',
                    background: '#f0f2f5',
                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                    overflow: 'auto', minHeight: isMobile ? '50vh' : 'auto'
                }}>
                    <div style={{
                        background: '#fff', boxShadow: '0 4px 24px rgba(0,0,0,0.1)',
                        borderRadius: '8px', overflow: 'hidden',
                        border: '1px solid #d1d5db', lineHeight: 0
                    }}>
                        <canvas ref={canvasRef} />
                    </div>
                </main>

                {/* Sidebar / Bottom panel */}
                <aside style={{
                    width: isDesktop ? `${sidebarW}px` : '100%',
                    borderLeft: isDesktop ? '1px solid #e5e7eb' : 'none',
                    borderTop: isDesktop ? 'none' : '1px solid #e5e7eb',
                    background: '#fff', overflow: 'auto',
                    maxHeight: isDesktop ? 'none' : '40vh',
                    padding: '16px'
                }}>
                    <h3 style={{ fontSize: '13px', fontWeight: 700, color: '#333', marginBottom: '12px', textTransform: 'uppercase', letterSpacing: '0.05em' }}>
                        🎨 Editor Tools
                    </h3>

                    {/* Add Text Section */}
                    <div style={{ marginBottom: '16px', borderBottom: '1px solid #f0f0f0', paddingBottom: '16px' }}>
                        <button onClick={() => setAddTextMode(!addTextMode)} style={{
                            width: '100%', padding: '10px', borderRadius: '8px',
                            border: '1px solid #e5e7eb', background: addTextMode ? '#eff6ff' : '#f9fafb',
                            cursor: 'pointer', fontWeight: 600, fontSize: '13px', color: '#333',
                            textAlign: 'left'
                        }}>
                            ✏️ {addTextMode ? 'Cancel' : 'Add Text'}
                        </button>

                        {addTextMode && (
                            <div style={{ marginTop: '12px', display: 'flex', flexDirection: 'column', gap: '8px' }}>
                                <input type="text" value={newText} onChange={e => setNewText(e.target.value)}
                                    placeholder="Enter text..."
                                    style={{
                                        width: '100%', padding: '8px 10px', borderRadius: '6px',
                                        border: '1px solid #d1d5db', fontSize: '14px', boxSizing: 'border-box'
                                    }} />
                                <div style={{ display: 'flex', gap: '6px' }}>
                                    <select value={newFontSize} onChange={e => setNewFontSize(e.target.value)}
                                        style={{ flex: 1, padding: '6px', borderRadius: '6px', border: '1px solid #d1d5db', fontSize: '12px' }}>
                                        {[14, 16, 18, 20, 24, 28, 32, 36, 40, 48, 56, 64, 72, 80].map(s =>
                                            <option key={s} value={s}>{s}px</option>
                                        )}
                                    </select>
                                    <input type="color" value={newFontColor} onChange={e => setNewFontColor(e.target.value)}
                                        style={{ width: '36px', height: '32px', borderRadius: '6px', border: '1px solid #d1d5db', cursor: 'pointer', padding: '2px' }} />
                                </div>
                                <select value={newFontFamily} onChange={e => setNewFontFamily(e.target.value)}
                                    style={{ width: '100%', padding: '6px', borderRadius: '6px', border: '1px solid #d1d5db', fontSize: '12px' }}>
                                    {fonts.map(f => <option key={f} value={f}>{f}</option>)}
                                </select>
                                <button onClick={handleAddText} style={{
                                    padding: '8px', borderRadius: '6px', border: 'none',
                                    background: '#2563eb', color: '#fff', fontWeight: 600,
                                    fontSize: '13px', cursor: 'pointer'
                                }}>Add Text to Canvas</button>
                            </div>
                        )}
                    </div>

                    {/* Selected text properties */}
                    {showTextPanel && selectedObj?._isEditorText && (
                        <div style={{ marginBottom: '16px', borderBottom: '1px solid #f0f0f0', paddingBottom: '16px' }}>
                            <h4 style={{ fontSize: '12px', fontWeight: 600, color: '#666', marginBottom: '8px', textTransform: 'uppercase' }}>Text Properties</h4>
                            <div style={{ display: 'flex', flexDirection: 'column', gap: '8px' }}>
                                <div>
                                    <label style={{ fontSize: '11px', color: '#888', display: 'block', marginBottom: '2px' }}>Font Family</label>
                                    <select value={textProps.fontFamily}
                                        onChange={e => updateTextProp('fontFamily', e.target.value)}
                                        style={{ width: '100%', padding: '6px', borderRadius: '6px', border: '1px solid #d1d5db', fontSize: '12px' }}>
                                        {fonts.map(f => <option key={f} value={f}>{f}</option>)}
                                    </select>
                                </div>
                                <div style={{ display: 'flex', gap: '6px' }}>
                                    <div style={{ flex: 1 }}>
                                        <label style={{ fontSize: '11px', color: '#888', display: 'block', marginBottom: '2px' }}>Size</label>
                                        <select value={textProps.fontSize}
                                            onChange={e => updateTextProp('fontSize', parseInt(e.target.value))}
                                            style={{ width: '100%', padding: '6px', borderRadius: '6px', border: '1px solid #d1d5db', fontSize: '12px' }}>
                                            {[14, 16, 18, 20, 24, 28, 32, 36, 40, 48, 56, 64, 72, 80].map(s =>
                                                <option key={s} value={s}>{s}px</option>
                                            )}
                                        </select>
                                    </div>
                                    <div>
                                        <label style={{ fontSize: '11px', color: '#888', display: 'block', marginBottom: '2px' }}>Color</label>
                                        <input type="color" value={textProps.fill}
                                            onChange={e => updateTextProp('fill', e.target.value)}
                                            style={{ width: '36px', height: '30px', borderRadius: '6px', border: '1px solid #d1d5db', cursor: 'pointer', padding: '2px' }} />
                                    </div>
                                </div>
                                <div style={{ display: 'flex', gap: '6px' }}>
                                    <button onClick={() => {
                                        const v = selectedObj.fontWeight === 'bold' ? 'normal' : 'bold';
                                        updateTextProp('fontWeight', v);
                                    }} style={{
                                        flex: 1, padding: '6px', borderRadius: '6px', cursor: 'pointer',
                                        border: '1px solid #d1d5db', fontWeight: 'bold', fontSize: '13px',
                                        background: selectedObj?.fontWeight === 'bold' ? '#dbeafe' : '#fff'
                                    }}>B</button>
                                    <button onClick={() => {
                                        const v = selectedObj.fontStyle === 'italic' ? 'normal' : 'italic';
                                        updateTextProp('fontStyle', v);
                                    }} style={{
                                        flex: 1, padding: '6px', borderRadius: '6px', cursor: 'pointer',
                                        border: '1px solid #d1d5db', fontStyle: 'italic', fontSize: '13px',
                                        background: selectedObj?.fontStyle === 'italic' ? '#dbeafe' : '#fff'
                                    }}>I</button>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Selected object actions */}
                    {selectedObj && (
                        <div style={{ marginBottom: '16px' }}>
                            <button onClick={handleDelete} style={{
                                width: '100%', padding: '8px', borderRadius: '8px',
                                border: '1px solid #fca5a5', background: '#fef2f2',
                                color: '#dc2626', fontWeight: 600, fontSize: '13px', cursor: 'pointer'
                            }}>🗑️ Delete Selected</button>
                        </div>
                    )}

                    {/* Instructions */}
                    <div style={{ background: '#f9fafb', borderRadius: '8px', padding: '12px', marginTop: '8px' }}>
                        <h4 style={{ fontSize: '11px', fontWeight: 600, color: '#888', marginBottom: '6px', textTransform: 'uppercase' }}>How to use</h4>
                        <ul style={{ fontSize: '11px', color: '#666', lineHeight: '1.8', paddingLeft: '14px', margin: 0 }}>
                            <li>Click <span style={{ color: '#dc2626', fontWeight: 600 }}>red zones</span> to upload images</li>
                            <li>Uploaded images can be moved & resized</li>
                            <li>Use "Add Text" to place editable text</li>
                            <li>Click text to edit font, size & color</li>
                            <li>Press Save or Next when done</li>
                        </ul>
                    </div>
                </aside>
            </div>
        </PageShell>
    );
};


// ════════════════════════════════════════════════════
// 6. SELECT SIZE
// ════════════════════════════════════════════════════
const SelectSize = () => {
    const navigate = useNavigate();
    const { selectedCard, selectedSize, setSelectedSize } = useApp();
    const { isDesktop } = useResponsive();

    const sizes = selectedCard?.sizes || [
        { id: '4x6', label: '4×6 inch', sublabel: 'Postcard', price: 99 },
        { id: '5x7', label: '5×7 inch', sublabel: 'Folded Card', price: 149 },
        { id: '6x8', label: '6×8 inch', sublabel: 'Standard', price: 199 },
        { id: '8x10', label: '8×10 inch', sublabel: 'Premium', price: 349 },
        { id: '12x18', label: '12×18 inch', sublabel: 'Poster', price: 599 },
    ];

    return (
        <PageShell>
            <Header title="Select Size" onBack={() => navigate(-1)} />
            <StepIndicator current={2} total={6} />

            <main style={{ flex: 1, padding: isDesktop ? '24px' : '16px' }}>
                <div style={{ display: 'flex', flexDirection: 'column', gap: '10px' }}>
                    {sizes.map(s => (
                        <button key={s.id} onClick={() => setSelectedSize(s)} style={{
                            display: 'flex', alignItems: 'center', justifyContent: 'space-between',
                            padding: isDesktop ? '18px 20px' : '16px',
                            borderRadius: '12px',
                            border: selectedSize?.id === s.id ? '2px solid #2563eb' : '2px solid #e5e7eb',
                            background: selectedSize?.id === s.id ? '#eff6ff' : '#fff',
                            cursor: 'pointer', transition: 'all 0.15s',
                            textAlign: 'left', minHeight: '56px'
                        }}>
                            <div>
                                <div style={{ fontWeight: 700, fontSize: isDesktop ? '16px' : '15px', color: '#111' }}>{s.label}</div>
                                <div style={{ fontSize: '12px', color: '#888', marginTop: '2px' }}>{s.sublabel}</div>
                            </div>
                            <div style={{ fontWeight: 700, fontSize: isDesktop ? '18px' : '16px', color: '#2563eb' }}>₹{s.price}</div>
                        </button>
                    ))}
                </div>
            </main>
            <BottomButton label="Continue" onClick={() => navigate('/order-confirm')} disabled={!selectedSize} />
        </PageShell>
    );
};


// ════════════════════════════════════════════════════
// 7. ORDER CONFIRM
// ════════════════════════════════════════════════════
const OrderConfirm = () => {
    const navigate = useNavigate();
    const { selectedCard, selectedSize, userImages } = useApp();
    const { isDesktop } = useResponsive();

    const price = selectedSize?.price || selectedCard?.price || 0;
    const tax = Math.round(price * 0.18);
    const total = price + tax;

    return (
        <PageShell>
            <Header title="Order Summary" onBack={() => navigate(-1)} />
            <StepIndicator current={3} total={6} />

            <main style={{ flex: 1, padding: isDesktop ? '24px' : '16px' }}>
                {/* Card info */}
                <div style={{
                    background: '#f8f9fb', borderRadius: '12px',
                    padding: isDesktop ? '20px' : '16px',
                    display: 'flex', gap: '14px', marginBottom: '16px'
                }}>
                    <div style={{
                        width: isDesktop ? '80px' : '72px', height: isDesktop ? '106px' : '96px',
                        borderRadius: '8px', background: '#e5e7eb', overflow: 'hidden', flexShrink: 0
                    }}>
                        {selectedCard?.image && <img src={selectedCard.image} alt="" style={{ width: '100%', height: '100%', objectFit: 'cover' }} />}
                    </div>
                    <div>
                        <h3 style={{ fontWeight: 700, fontSize: '15px', color: '#111' }}>{selectedCard?.name || 'Custom Design'}</h3>
                        <p style={{ fontSize: '12px', color: '#888', marginTop: '6px' }}>Size: {selectedSize?.label || 'N/A'}</p>
                        <p style={{ fontSize: '12px', color: '#888' }}>Photos: {userImages?.length || 0}</p>
                    </div>
                </div>

                {/* Price */}
                <div style={{ borderRadius: '12px', border: '1px solid #e5e7eb', overflow: 'hidden', marginBottom: '16px' }}>
                    <div style={{ padding: '14px 16px', display: 'flex', justifyContent: 'space-between', borderBottom: '1px solid #f0f0f0' }}>
                        <span style={{ color: '#555', fontSize: '14px' }}>Subtotal</span>
                        <span style={{ fontWeight: 600, color: '#111' }}>₹{price}</span>
                    </div>
                    <div style={{ padding: '14px 16px', display: 'flex', justifyContent: 'space-between', borderBottom: '1px solid #f0f0f0' }}>
                        <span style={{ color: '#555', fontSize: '14px' }}>Tax (18%)</span>
                        <span style={{ fontWeight: 600, color: '#111' }}>₹{tax}</span>
                    </div>
                    <div style={{ padding: '14px 16px', display: 'flex', justifyContent: 'space-between', background: '#f0f5ff' }}>
                        <span style={{ fontWeight: 700, fontSize: '16px', color: '#111' }}>Total</span>
                        <span style={{ fontWeight: 800, fontSize: '18px', color: '#2563eb' }}>₹{total}</span>
                    </div>
                </div>

                {/* Fulfillment */}
                <div>
                    <h3 style={{ fontWeight: 600, fontSize: '13px', color: '#888', textTransform: 'uppercase', letterSpacing: '0.05em', marginBottom: '8px' }}>Fulfillment</h3>
                    <div style={{ display: 'flex', flexDirection: 'column', gap: '8px' }}>
                        <label style={{ display: 'flex', alignItems: 'center', gap: '10px', padding: '14px 16px', borderRadius: '10px', border: '2px solid #2563eb', background: '#eff6ff', cursor: 'pointer', minHeight: '52px' }}>
                            <input type="radio" name="fulfill" defaultChecked style={{ accentColor: '#2563eb' }} />
                            <div>
                                <div style={{ fontWeight: 600, fontSize: '14px', color: '#111' }}>Store Pickup</div>
                                <div style={{ fontSize: '11px', color: '#888' }}>Ready in 2-3 business days</div>
                            </div>
                        </label>
                        <label style={{ display: 'flex', alignItems: 'center', gap: '10px', padding: '14px 16px', borderRadius: '10px', border: '2px solid #e5e7eb', cursor: 'pointer', minHeight: '52px' }}>
                            <input type="radio" name="fulfill" style={{ accentColor: '#2563eb' }} />
                            <div>
                                <div style={{ fontWeight: 600, fontSize: '14px', color: '#111' }}>Home Delivery</div>
                                <div style={{ fontSize: '11px', color: '#888' }}>5-7 business days · ₹50 shipping</div>
                            </div>
                        </label>
                    </div>
                </div>
            </main>
            <BottomButton label="Proceed to Sign Up" onClick={() => navigate('/signup')} />
        </PageShell>
    );
};


// ════════════════════════════════════════════════════
// 8. SIGN UP
// ════════════════════════════════════════════════════
const SignUp = () => {
    const navigate = useNavigate();
    const { user, setUser } = useApp();
    const { isDesktop } = useResponsive();
    const [name, setName] = useState(user?.name || '');
    const [email, setEmail] = useState(user?.email || '');
    const [phone, setPhone] = useState(user?.phone || '');

    const handleContinue = () => { setUser({ name, email, phone }); navigate('/payment'); };
    const isValid = name.trim() && email.trim() && phone.trim();

    const inputStyle = {
        width: '100%', padding: isDesktop ? '14px 16px' : '12px 14px',
        borderRadius: '10px', border: '2px solid #e5e7eb',
        fontSize: '16px', outline: 'none', boxSizing: 'border-box',
        transition: 'border-color 0.2s', minHeight: '48px'
    };

    return (
        <PageShell>
            <Header title="Your Details" onBack={() => navigate(-1)} />
            <StepIndicator current={4} total={6} />

            <main style={{ flex: 1, padding: isDesktop ? '24px 32px' : '16px' }}>
                <p style={{ fontSize: '13px', color: '#888', marginBottom: '20px' }}>
                    Enter your details to receive order updates. No account needed!
                </p>
                <div style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
                    <div>
                        <label style={{ fontSize: '12px', fontWeight: 600, color: '#555', marginBottom: '6px', display: 'block' }}>Full Name *</label>
                        <input type="text" value={name} onChange={e => setName(e.target.value)} placeholder="John Doe" style={inputStyle} />
                    </div>
                    <div>
                        <label style={{ fontSize: '12px', fontWeight: 600, color: '#555', marginBottom: '6px', display: 'block' }}>Email *</label>
                        <input type="email" value={email} onChange={e => setEmail(e.target.value)} placeholder="john@example.com" style={inputStyle} />
                    </div>
                    <div>
                        <label style={{ fontSize: '12px', fontWeight: 600, color: '#555', marginBottom: '6px', display: 'block' }}>Phone *</label>
                        <input type="tel" value={phone} onChange={e => setPhone(e.target.value)} placeholder="+91 98765 43210" style={inputStyle} />
                    </div>
                </div>
                <div style={{ marginTop: '20px', background: '#f0fdf4', border: '1px solid #bbf7d0', borderRadius: '10px', padding: '12px 14px' }}>
                    <p style={{ fontSize: '12px', color: '#15803d' }}>🔒 Your info is only used for order updates and receipt. No spam!</p>
                </div>
            </main>
            <BottomButton label="Continue to Payment" onClick={handleContinue} disabled={!isValid} />
        </PageShell>
    );
};


// ════════════════════════════════════════════════════
// 9. PAYMENT
// ════════════════════════════════════════════════════
const Payment = () => {
    const navigate = useNavigate();
    const { selectedCard, selectedSize, user, setOrderId, storeId } = useApp();
    const { isDesktop } = useResponsive();
    const [processing, setProcessing] = useState(false);

    const price = selectedSize?.price || selectedCard?.price || 0;
    const tax = Math.round(price * 0.18);
    const total = price + tax;

    const handlePayment = async () => {
        setProcessing(true);
        try {
            const res = await fetch('/custom/public/api/noritsu/v1/orders', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    store_id: storeId, template_id: selectedCard?.id,
                    size: selectedSize?.id, customer: user,
                    total, payment_method: 'card'
                })
            });
            if (res.ok) {
                const data = await res.json();
                setOrderId(data.order_id || data.id || 'ORD-' + Date.now());
            } else { setOrderId('ORD-' + Date.now()); }
            navigate('/order-done');
        } catch (e) {
            setOrderId('ORD-' + Date.now());
            navigate('/order-done');
        }
    };

    const payBtnStyle = (dark) => ({
        display: 'flex', alignItems: 'center', gap: '12px',
        padding: isDesktop ? '16px 20px' : '14px 16px',
        borderRadius: '12px',
        border: dark ? '2px solid #111' : '2px solid #e5e7eb',
        background: dark ? '#000' : '#fff',
        color: dark ? '#fff' : '#333',
        cursor: 'pointer', fontSize: '15px', fontWeight: 600,
        width: '100%', minHeight: '52px', transition: 'all 0.15s',
        textAlign: 'left'
    });

    return (
        <PageShell>
            <Header title="Payment" onBack={() => navigate(-1)} />
            <StepIndicator current={5} total={6} />

            <main style={{ flex: 1, padding: isDesktop ? '24px 32px' : '16px' }}>
                <div style={{ textAlign: 'center', padding: isDesktop ? '32px 0' : '24px 0', marginBottom: '20px' }}>
                    <p style={{ fontSize: '13px', color: '#888' }}>Amount to Pay</p>
                    <p style={{ fontSize: isDesktop ? '42px' : '36px', fontWeight: 800, color: '#111', margin: '4px 0' }}>₹{total}</p>
                    <p style={{ fontSize: '12px', color: '#aaa' }}>Incl. ₹{tax} tax</p>
                </div>

                <div style={{ display: 'flex', flexDirection: 'column', gap: '10px' }}>
                    <button style={payBtnStyle(true)}>
                        <span style={{ fontSize: '20px' }}></span> Pay with Apple Pay
                    </button>
                    <button style={payBtnStyle(false)}>
                        <span style={{ fontSize: '20px' }}>💳</span> Credit / Debit Card
                    </button>
                    <button style={payBtnStyle(false)}>
                        <span style={{ fontSize: '20px' }}>📱</span> Google Pay / UPI
                    </button>
                </div>

                <div style={{ marginTop: '16px', display: 'flex', alignItems: 'center', gap: '8px' }}>
                    <input type="checkbox" id="refundPolicy" defaultChecked style={{ accentColor: '#2563eb', width: '18px', height: '18px' }} />
                    <label htmlFor="refundPolicy" style={{ fontSize: '12px', color: '#888' }}>
                        I agree to the <a href="#" style={{ color: '#2563eb', textDecoration: 'underline' }}>Refund Policy</a>
                    </label>
                </div>
            </main>

            <div style={{
                padding: isDesktop ? '16px 32px' : '12px 16px',
                paddingBottom: `max(${isDesktop ? '16px' : '12px'}, env(safe-area-inset-bottom))`,
                borderTop: '1px solid #f0f0f0', background: '#fff'
            }}>
                <button onClick={handlePayment} disabled={processing} style={{
                    width: '100%', padding: isDesktop ? '18px' : '16px',
                    borderRadius: '12px', border: 'none',
                    fontSize: '16px', fontWeight: 700,
                    cursor: processing ? 'not-allowed' : 'pointer',
                    background: processing ? '#93c5fd' : '#2563eb', color: '#fff',
                    boxShadow: '0 4px 14px rgba(37,99,235,0.3)', minHeight: '52px'
                }}>
                    {processing ? 'Processing...' : `Pay ₹${total}`}
                </button>
            </div>
        </PageShell>
    );
};


// ════════════════════════════════════════════════════
// 10. ORDER DONE
// ════════════════════════════════════════════════════
const OrderDone = () => {
    const navigate = useNavigate();
    const { orderId, user, resetOrder } = useApp();
    const { isDesktop } = useResponsive();

    const handleNewOrder = () => { resetOrder(); navigate('/'); };

    return (
        <PageShell>
            <main style={{
                flex: 1, display: 'flex', flexDirection: 'column',
                alignItems: 'center', justifyContent: 'center',
                padding: isDesktop ? '48px 40px' : '32px 24px', textAlign: 'center'
            }}>
                <div style={{
                    width: isDesktop ? '96px' : '80px', height: isDesktop ? '96px' : '80px',
                    borderRadius: '50%', background: 'linear-gradient(135deg, #0ea5e9, #16a34a)',
                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                    marginBottom: '24px', boxShadow: '0 8px 24px rgba(34,197,94,0.3)',
                    animation: 'scaleIn 0.5s ease-out'
                }}>
                    <svg width={isDesktop ? '48' : '40'} height={isDesktop ? '48' : '40'} fill="white" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z" /></svg>
                </div>

                <h1 style={{ fontSize: isDesktop ? '28px' : '24px', fontWeight: 800, color: '#111', marginBottom: '8px', animation: 'fadeInUp 0.5s ease-out 0.1s both' }}>Order Confirmed!</h1>
                <p style={{ color: '#888', fontSize: '14px', marginBottom: '24px', maxWidth: '360px', animation: 'fadeInUp 0.5s ease-out 0.2s both' }}>
                    Your order has been placed and a confirmation email will be sent to <strong>{user?.email || 'your email'}</strong>.
                </p>

                <div style={{ background: '#f8f9fb', borderRadius: '12px', padding: '20px', width: '100%', maxWidth: '320px', marginBottom: '24px', animation: 'fadeInUp 0.5s ease-out 0.3s both' }}>
                    <p style={{ fontSize: '12px', color: '#aaa', marginBottom: '4px' }}>Order Number</p>
                    <p style={{ fontSize: '22px', fontWeight: 800, color: '#2563eb', letterSpacing: '1px' }}>{orderId || 'N/A'}</p>
                </div>

                <div style={{ width: '100%', maxWidth: '320px', marginBottom: '32px', textAlign: 'left', animation: 'fadeInUp 0.5s ease-out 0.4s both' }}>
                    <h3 style={{ fontSize: '12px', fontWeight: 600, color: '#888', textTransform: 'uppercase', marginBottom: '12px' }}>Status</h3>
                    {[
                        { label: 'Order Received', active: true },
                        { label: 'Printing', active: false },
                        { label: 'Shipped', active: false },
                        { label: 'Ready for Pickup', active: false },
                    ].map((step, i) => (
                        <div key={i} style={{ display: 'flex', alignItems: 'center', gap: '10px', marginBottom: '10px' }}>
                            <div style={{
                                width: '22px', height: '22px', borderRadius: '50%',
                                background: step.active ? '#0ea5e9' : '#e5e7eb',
                                display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0
                            }}>
                                {step.active && <svg width="10" height="10" fill="white" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z" /></svg>}
                            </div>
                            <span style={{ fontSize: '14px', color: step.active ? '#111' : '#aaa', fontWeight: step.active ? 600 : 400 }}>{step.label}</span>
                        </div>
                    ))}
                </div>

                <button onClick={handleNewOrder} style={{
                    padding: '14px 32px', borderRadius: '12px', border: '2px solid #e5e7eb',
                    background: '#fff', fontSize: '15px', fontWeight: 600,
                    cursor: 'pointer', color: '#333', minHeight: '48px',
                    transition: 'all 0.2s'
                }}>
                    Start New Order
                </button>
            </main>
            <footer style={{ padding: '16px', textAlign: 'center', fontSize: '11px', color: '#bbb' }}>Powered by Noritsu</footer>
        </PageShell>
    );
};


// ════════════════════════════════════════════════════
// APP + ROUTES
// ════════════════════════════════════════════════════
const App = () => (
    <AppProvider>
        <BrowserRouter basename="/custom/public/noritsu">
            <Routes>
                <Route path="/" element={<Home />} />
                <Route path="/start" element={<StartOrder />} />
                <Route path="/cards" element={<CardList />} />
                <Route path="/own-design" element={<OwnDesign />} />
                <Route path="/editor" element={<EditDesign />} />
                <Route path="/select-size" element={<SelectSize />} />
                <Route path="/order-confirm" element={<OrderConfirm />} />
                <Route path="/signup" element={<SignUp />} />
                <Route path="/payment" element={<Payment />} />
                <Route path="/order-done" element={<OrderDone />} />
            </Routes>
        </BrowserRouter>
    </AppProvider>
);

// ──── Mount ────
const container = document.getElementById('noritsu-app');
if (container) {
    const root = createRoot(container);
    root.render(<StrictMode><App /></StrictMode>);
}
