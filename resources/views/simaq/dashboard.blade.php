<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-green-800 leading-tight">
            {{ __('Dashboard SIMAQ') }}
        </h2>
    </x-slot>

    <!-- WRAPPER BACKGROUND ORANYE LEMBUT -->
    <div class="py-12 bg-orange-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Welcome Banner -->
            <div class="bg-green-700 overflow-hidden shadow-lg sm:rounded-2xl mb-8 border-b-4 border-orange-500 relative">
                <div class="absolute top-0 right-0 opacity-10 pointer-events-none transform translate-x-4 -translate-y-8">
                    <svg class="w-48 h-48 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 22h20L12 2z"/></svg>
                </div>
                
                <div class="p-8 text-white relative z-10 flex flex-col md:flex-row justify-between items-center">
                    <div>
                        <h3 class="text-3xl font-extrabold drop-shadow-md">Ahlan wa Sahlan, {{ auth()->user()->name }}</h3>
                        <p class="mt-2 text-green-100 text-lg">Selamat datang di Panel Guru Tahsin & Tahfizh (SIMAQ).</p>
                    </div>
                    <!-- Statistik Cepat -->
                    <div class="mt-4 md:mt-0 flex gap-4 text-center">
                        <div class="bg-green-800 bg-opacity-50 rounded-lg p-3 min-w-[100px]">
                            <div class="text-3xl font-bold text-orange-400">{{ $totalSantri }}</div>
                            <div class="text-xs text-green-200 uppercase tracking-widest mt-1">Santri Aktif</div>
                        </div>
                        <div class="bg-green-800 bg-opacity-50 rounded-lg p-3 min-w-[100px]">
                            <div class="text-3xl font-bold text-orange-400">{{ $totalPenilaian }}</div>
                            <div class="text-xs text-green-200 uppercase tracking-widest mt-1">Total Setoran</div>
                        </div>
                    </div>
                </div>
            </div>

            <h4 class="text-xl font-extrabold text-orange-800 mb-4 px-1 flex items-center">
                <span class="w-2 h-6 bg-green-600 rounded mr-3"></span> Menu Utama Mutaba'ah
            </h4>

            <!-- Grid Menu Utama (Dibuat sedikit lebih compact agar hemat ruang) -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <!-- 1. Setoran Harian -->
                <a href="{{ route('simaq.harian.index') }}" class="bg-white rounded-xl p-5 border-t-4 border-orange-500 hover:shadow-lg transition-all group block text-center">
                    <div class="text-3xl mb-2 group-hover:scale-110 transition-transform duration-300">📖</div>
                    <div class="font-bold text-green-800">Setoran Harian</div>
                </a>
                <!-- 2. Ujian Pemantapan -->
                <a href="{{ route('simaq.pemantapan.index') }}" class="bg-white rounded-xl p-5 border-t-4 border-orange-500 hover:shadow-lg transition-all group block text-center">
                    <div class="text-3xl mb-2 group-hover:scale-110 transition-transform duration-300">🎯</div>
                    <div class="font-bold text-green-800">Pemantapan</div>
                </a>
                <!-- 3. Imtihan Tasmi' -->
                <a href="{{ route('simaq.tasmi.index') }}" class="bg-white rounded-xl p-5 border-t-4 border-orange-500 hover:shadow-lg transition-all group block text-center">
                    <div class="text-3xl mb-2 group-hover:scale-110 transition-transform duration-300">🎤</div>
                    <div class="font-bold text-green-800">Imtihan Tasmi'</div>
                </a>
                <!-- 4. Jamiyyatul Huffazh -->
                <a href="{{ route('simaq.huffazh.index') }}" class="bg-white rounded-xl p-5 border-t-4 border-orange-500 hover:shadow-lg transition-all group block text-center">
                    <div class="text-3xl mb-2 group-hover:scale-110 transition-transform duration-300">👑</div>
                    <div class="font-bold text-green-800">J. Huffazh</div>
                </a>
            </div>

            <!-- GRAFIK & LEADERBOARD SECTION -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Kiri: Grafik 7 Hari Terakhir (Mengambil porsi 2 kolom) -->
                <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                    <h4 class="text-lg font-bold text-gray-800 mb-4 flex items-center justify-between">
                        <span>Tren Setoran (7 Hari Terakhir)</span>
                        <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded font-semibold">Live Data</span>
                    </h4>
                    <!-- Wadah Grafik -->
                    <div class="relative h-64 w-full">
                        <canvas id="setoranChart"></canvas>
                    </div>
                </div>

                <!-- Kanan: Leaderboard Santri Terajin -->
                <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 flex flex-col">
                    <h4 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">🏆 Leaderboard Santri</h4>
                    
                    <div class="flex-1 overflow-y-auto">
                        @forelse($leaderboard as $index => $juara)
                            <div class="flex items-center p-3 mb-2 rounded-lg {{ $index === 0 ? 'bg-orange-50 border border-orange-200' : 'hover:bg-gray-50' }} transition-colors">
                                <!-- Ranking Angka/Medali -->
                                <div class="w-8 flex-shrink-0 text-center font-black {{ $index === 0 ? 'text-2xl text-yellow-500' : ($index === 1 ? 'text-xl text-gray-400' : ($index === 2 ? 'text-xl text-amber-600' : 'text-lg text-gray-300')) }}">
                                    {{ $index === 0 ? '🥇' : ($index === 1 ? '🥈' : ($index === 2 ? '🥉' : $index + 1)) }}
                                </div>
                                
                                <!-- Info Santri -->
                                <div class="ml-3 flex-1">
                                    <p class="text-sm font-bold text-gray-800 line-clamp-1">{{ $juara->nama ?? $juara->nama_lengkap }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-[10px] bg-green-100 text-green-800 px-2 py-0.5 rounded-full font-semibold">Juz {{ $juara->simaq_juz_tercapai ?? 0 }}</span>
                                        <span class="text-[10px] text-gray-500">{{ $juara->simaq_total_setoran }} Setoran</span>
                                    </div>
                                </div>

                                <!-- Bintang Rata-rata -->
                                <div class="text-right">
                                    <div class="text-yellow-400 text-sm">
                                        @for($i = 0; $i < round($juara->simaq_total_bintang); $i++) ★ @endfor
                                    </div>
                                    <div class="text-[10px] text-gray-400 mt-0.5">{{ number_format($juara->simaq_total_nilai, 1) }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-gray-500 text-sm mt-10 italic">
                                Belum ada data leaderboard.<br>Mulai input nilai setoran santri.
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

        </div>
    </div>

    <!-- Script Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('setoranChart').getContext('2d');
            
            // Gradient untuk bar chart agar lebih estetik
            const gradient = ctx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, '#f97316'); // orange-500
            gradient.addColorStop(1, '#15803d'); // green-700

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($chartLabels) !!}, // Tanggal 7 hari terakhir
                    datasets: [{
                        label: 'Jumlah Setoran Hafalan',
                        data: {!! json_encode($chartData) !!}, // Data dinamis dari database
                        backgroundColor: gradient,
                        borderRadius: 8,
                        borderSkipped: false,
                        barThickness: 30
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(21, 128, 61, 0.9)', // warna tooltip hijau
                            padding: 12,
                            titleFont: { size: 14 },
                            bodyFont: { size: 13 }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1, color: '#6b7280' },
                            grid: { color: '#f3f4f6', drawBorder: false }
                        },
                        x: {
                            ticks: { color: '#6b7280', font: { weight: 'bold' } },
                            grid: { display: false, drawBorder: false }
                        }
                    }
                }
            });
        });
    </script>
</x-app-layout>