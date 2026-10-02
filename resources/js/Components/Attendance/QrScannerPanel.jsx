import { useEffect, useId, useRef, useState } from 'react';

export default function QrScannerPanel({
    disabled,
    hasActiveSession,
    onScan,
}) {
    const scannerId = useId().replace(/:/g, '-');
    const scannerRef = useRef(null);
    const scannerModuleRef = useRef(null);
    const scanLockRef = useRef(false);
    const [scannerState, setScannerState] = useState('idle');
    const [scannerMessage, setScannerMessage] = useState('Siap memulai kamera untuk membaca QR siswa.');
    const [cameraId, setCameraId] = useState('');
    const [cameras, setCameras] = useState([]);

    useEffect(() => {
        return () => {
            stopScanner(true);
        };
    }, []);

    async function ensureScannerModule() {
        if (!scannerModuleRef.current) {
            scannerModuleRef.current = await import('html5-qrcode');
        }

        return scannerModuleRef.current;
    }

    async function loadCameras() {
        const { Html5Qrcode } = await ensureScannerModule();
        const detectedCameras = await Html5Qrcode.getCameras();

        setCameras(detectedCameras);

        if (!cameraId && detectedCameras[0]?.id) {
            setCameraId(detectedCameras[0].id);
        }

        return detectedCameras;
    }

    async function startScanner() {
        if (disabled || !hasActiveSession) {
            setScannerMessage('Pilih sesi aktif terlebih dahulu sebelum menyalakan kamera.');
            return;
        }

        try {
            setScannerState('loading');
            setScannerMessage('Meminta akses kamera...');

            const { Html5Qrcode } = await ensureScannerModule();
            const detectedCameras = await loadCameras();
            const resolvedCameraId = cameraId || detectedCameras[0]?.id;

            if (!resolvedCameraId) {
                setScannerState('error');
                setScannerMessage('Tidak ada kamera yang terdeteksi di perangkat ini.');
                return;
            }

            if (!scannerRef.current) {
                scannerRef.current = new Html5Qrcode(scannerId, {
                    verbose: false,
                });
            }

            await scannerRef.current.start(
                resolvedCameraId,
                {
                    fps: 10,
                    qrbox: { width: 220, height: 220 },
                    aspectRatio: 1.2,
                },
                async (decodedText) => {
                    if (scanLockRef.current) {
                        return;
                    }

                    scanLockRef.current = true;
                    setScannerMessage(`QR terbaca: ${decodedText.slice(0, 24)}${decodedText.length > 24 ? '...' : ''}`);

                    try {
                        await onScan(decodedText);
                    } finally {
                        window.setTimeout(() => {
                            scanLockRef.current = false;
                        }, 1200);
                    }
                },
                () => {}
            );

            setScannerState('running');
            setScannerMessage('Kamera aktif. Arahkan QR siswa ke area scanner.');
        } catch (error) {
            setScannerState('error');
            setScannerMessage(error?.message || 'Kamera gagal dijalankan.');
        }
    }

    async function stopScanner(silent = false) {
        scanLockRef.current = false;

        if (!scannerRef.current) {
            if (!silent) {
                setScannerState('idle');
                setScannerMessage('Kamera dihentikan. Kamu bisa pakai input manual sebagai fallback.');
            }
            return;
        }

        try {
            const state = scannerRef.current.getState?.();

            if (state === 2 || state === 3) {
                await scannerRef.current.stop();
            }

            await scannerRef.current.clear();
        } catch {
            // Swallow cleanup errors so the fallback UI remains usable.
        } finally {
            scannerRef.current = null;

            if (!silent) {
                setScannerState('idle');
                setScannerMessage('Kamera dihentikan. Kamu bisa pakai input manual sebagai fallback.');
            }
        }
    }

    return (
        <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                <div>
                    <h4 className="text-base font-semibold text-slate-900">Scanner kamera</h4>
                    <p className="mt-1 text-sm text-slate-600">
                        Mode utama untuk membaca QR siswa secara langsung. Input manual tetap tersedia jika kamera bermasalah.
                    </p>
                </div>
                <div className="flex gap-2">
                    <button
                        type="button"
                        onClick={startScanner}
                        disabled={disabled || scannerState === 'loading' || scannerState === 'running'}
                        className="rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        Nyalakan Kamera
                    </button>
                    <button
                        type="button"
                        onClick={() => stopScanner()}
                        disabled={scannerState !== 'running'}
                        className="rounded-full border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        Hentikan
                    </button>
                </div>
            </div>

            <div className="mt-4 grid gap-4 lg:grid-cols-[1.2fr_0.8fr]">
                <div>
                    <div
                        id={scannerId}
                        className="min-h-72 overflow-hidden rounded-2xl border border-dashed border-slate-300 bg-white"
                    />
                    <p className="mt-3 text-sm text-slate-600">{scannerMessage}</p>
                </div>

                <div className="space-y-4">
                    <label className="block text-sm font-medium text-slate-700">
                        Pilih kamera
                        <select
                            value={cameraId}
                            onChange={(event) => setCameraId(event.target.value)}
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                            disabled={scannerState === 'running'}
                        >
                            <option value="">Default kamera</option>
                            {cameras.map((camera) => (
                                <option key={camera.id} value={camera.id}>
                                    {camera.label || `Camera ${camera.id}`}
                                </option>
                            ))}
                        </select>
                    </label>

                    <div className="rounded-2xl bg-white p-4">
                        <p className="text-sm font-medium text-slate-900">Status</p>
                        <p className="mt-2 text-sm text-slate-600">
                            {scannerState === 'running' && 'Kamera aktif dan siap scan.'}
                            {scannerState === 'loading' && 'Sedang menyiapkan kamera.'}
                            {scannerState === 'error' && 'Scanner mengalami kendala. Gunakan fallback manual bila perlu.'}
                            {scannerState === 'idle' && 'Scanner belum aktif.'}
                        </p>
                    </div>

                    {!hasActiveSession && (
                        <div className="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-700">
                            Scanner akan aktif setelah kamu memilih sesi absensi yang masih terbuka.
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
