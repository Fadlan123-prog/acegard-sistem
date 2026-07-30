@extends('frontend.layout.index')
@section('title','Acegard - Kaca Film Nano Ceramic')

@extends('frontend.layout.index')

@section('title', 'Acegard - Under Maintenance')

@section('content')

<section class="maintenance-page">
    <div class="maintenance-wrapper">
        <div class="maintenance-card">

            <div class="logo-wrapper">
                <img src="{{ asset('assets/images/logo.png') }}" alt="Acegard Logo" class="maintenance-logo">
            </div>

            <div class="maintenance-badge">
                UNDER MAINTENANCE
            </div>

            <h1 class="maintenance-title">
                Website Sedang Dalam Perbaikan
            </h1>

            <p class="maintenance-text">
                Mohon maaf, saat ini website Acegard sedang dalam proses pembaruan sistem
                untuk memberikan pengalaman yang lebih baik kepada pelanggan.
            </p>

            <p class="maintenance-subtext">
                Untuk sementara, Anda tetap bisa menghubungi kami melalui WhatsApp,
                mengecek warranty, atau mengajukan warranty claim melalui tombol di bawah ini.
            </p>

            <div class="maintenance-actions">
                <a
                    href="https://wa.me/6282121212093?text=Halo%20Acegard,%20saya%20ingin%20konsultasi"
                    target="_blank"
                    class="btn-maintenance-primary whatsapp-btn"
                >
                    Hubungi WhatsApp
                </a>

                {{-- <a
                    href="{{ route('warranty.check') }}"
                    class="btn-maintenance-success"
                >
                    Cek Warranty
                </a> --}}

                <a
                    href="{{ route('warranty.form') }}"
                    class="btn-maintenance-secondary"
                >
                    Warranty Claim
                </a>
            </div>

            <div class="maintenance-info">
                <div>
                    <strong>Acegard Nano Ceramic Film</strong>
                </div>
                <div>
                    Kaca Film Mobil & Gedung
                </div>
            </div>

        </div>
    </div>
</section>

<style>
    .maintenance-page {
        min-height: 100vh;
        background:
            radial-gradient(circle at top left, rgba(37, 99, 235, 0.16), transparent 35%),
            radial-gradient(circle at bottom right, rgba(15, 23, 42, 0.18), transparent 35%),
            #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 16px;
    }

    .maintenance-wrapper {
        width: 100%;
        max-width: 760px;
        margin: 0 auto;
    }

    .maintenance-card {
        background: #ffffff;
        border-radius: 28px;
        padding: 48px 36px;
        text-align: center;
        box-shadow: 0 24px 70px rgba(15, 23, 42, 0.12);
        border: 1px solid rgba(226, 232, 240, 0.9);
    }

    .logo-wrapper {
        margin-bottom: 24px;
    }

    .maintenance-logo {
        max-width: 220px;
        width: 100%;
        height: auto;
    }

    .maintenance-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 18px;
        border-radius: 999px;
        background: #eff6ff;
        color: #2563eb;
        font-weight: 700;
        font-size: 13px;
        letter-spacing: 1px;
        margin-bottom: 18px;
    }

    .maintenance-title {
        font-size: 38px;
        line-height: 1.2;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 16px;
    }

    .maintenance-text {
        font-size: 17px;
        line-height: 1.7;
        color: #475569;
        max-width: 620px;
        margin: 0 auto 12px;
    }

    .maintenance-subtext {
        font-size: 15px;
        line-height: 1.7;
        color: #64748b;
        max-width: 620px;
        margin: 0 auto 28px;
    }

    .maintenance-actions {
        display: flex;
        justify-content: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 28px;
    }

    .btn-maintenance-primary,
    .btn-maintenance-secondary,
    .btn-maintenance-success {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 48px;
        padding: 12px 22px;
        border-radius: 12px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .btn-maintenance-primary {
        background: #2563eb;
        color: #ffffff;
        border: 1px solid #2563eb;
    }

    .btn-maintenance-primary:hover {
        background: #1d4ed8;
        color: #ffffff;
        transform: translateY(-1px);
    }

    .btn-maintenance-success {
        background: #16a34a;
        color: #ffffff;
        border: 1px solid #16a34a;
    }

    .btn-maintenance-success:hover {
        background: #15803d;
        color: #ffffff;
        transform: translateY(-1px);
    }

    .btn-maintenance-secondary {
        background: #ffffff;
        color: #2563eb;
        border: 1px solid #bfdbfe;
    }

    .btn-maintenance-secondary:hover {
        background: #eff6ff;
        color: #1d4ed8;
        transform: translateY(-1px);
    }

    .maintenance-info {
        padding-top: 22px;
        border-top: 1px solid #e2e8f0;
        color: #64748b;
        font-size: 14px;
        line-height: 1.7;
    }

    .maintenance-info strong {
        color: #0f172a;
    }

    @media (max-width: 576px) {
        .maintenance-card {
            padding: 36px 22px;
            border-radius: 22px;
        }

        .maintenance-logo {
            max-width: 180px;
        }

        .maintenance-title {
            font-size: 28px;
        }

        .maintenance-text {
            font-size: 15px;
        }

        .maintenance-subtext {
            font-size: 14px;
        }

        .maintenance-actions {
            flex-direction: column;
        }

        .btn-maintenance-primary,
        .btn-maintenance-secondary,
        .btn-maintenance-success {
            width: 100%;
        }
    }
</style>

@endsection

{{-- @section('content')


<section id="home" class="hero">
  <div class="container">
    <div class="row align-items-center g-4">
      <div class="col-lg-6">
        <span class="badge badge-soft rounded-pill px-3 py-2 mb-3">
          ACEGARD NANO CERAMIC FILM
        </span>

        <h1 class="hero-title display-5 mb-3">
          KACA FILM <span class="text-primary">NANO CERAMIC</span><br class="d-none d-md-block">
          DENGAN <span class="text-primary">SOLAR REJECT</span> TERBAIK
        </h1>

        <p class="text-secondary mb-4">
          Tingkatkan kenyamanan dengan penolakan panas tinggi, visibilitas jernih,
          dan perlindungan UV. Cocok untuk mobil & gedung.
        </p>

        <div class="d-flex gap-2 flex-wrap">
          <a class="btn btn-brand btn-lg" href="#contact">Order Sekarang</a>
          <a class="btn btn-outline-secondary btn-lg" href="#product">More Information</a>
        </div>


        <div class="row mt-4 g-3">
          <div class="col-md-6">
            <div class="d-flex gap-3 align-items-start">
              <div class="icon-pill">📍</div>
              <div>
                <div class="fw-bold">Location</div>
                <div class="small text-secondary">Kota Tangerang, Banten</div>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="d-flex gap-3 align-items-start">
              <div class="icon-pill">💬</div>
              <div>
                <div class="fw-bold">WhatsApp</div>
                <div class="small text-secondary">08xx-xxxx-xxxx</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-6 text-center">
        <img class="img-fluid" src="{{ asset('assets/img/hero-car.png') }}"
             alt="Acegard" style="max-height:360px;">
      </div>
    </div>
  </div>
</section>


<section id="product" class="section">
  <div class="container">
    <div class="row g-4 align-items-center">

      <div class="col-lg-6 order-2 order-lg-1">
        <img class="img-fluid rounded-4 shadow-sm" src="{{ asset('assets/img/product-car.png') }}" alt="Product">
      </div>

      <div class="col-lg-6 order-1 order-lg-2">
        <h2 class="fw-bold mb-3">Teknologi Night Vision Dengan Daya Tolak Panas 99,9%</h2>
        <p class="text-secondary">
          Nano ceramic film membantu mengurangi panas, menjaga interior lebih sejuk,
          dan meningkatkan kenyamanan. Tetap jernih saat malam (night vision).
        </p>

        <div class="row g-3 mt-2">
          <div class="col-sm-6">
            <x-feature-card title="High Heat Rejection" desc="Tolak panas tinggi untuk kabin lebih adem." icon="🔥" />
          </div>
          <div class="col-sm-6">
            <x-feature-card title="UV Protection" desc="Membantu melindungi kulit & interior." icon="🛡️" />
          </div>
        </div>

        <a class="btn btn-primary mt-4" href="#contact">Consultation</a>
      </div>
    </div>
  </div>
</section>


<section class="section section-soft">
  <div class="container">
    <x-section-heading
      title="Kenapa Memilih Kaca Film Dari Acegard?"
      subtitle="Beberapa alasan utama yang bikin customer nyaman dan percaya." />


    <div class="row g-3">
      <div class="col-md-4 col-sm-6">
        <x-feature-card title="Bersertifikat & Bergaransi" desc="Garansi produk & pemasangan sesuai ketentuan." />
      </div>
      <div class="col-md-4 col-sm-6">
        <x-feature-card title="Anti Panas Maksimal" desc="Solar reject tinggi untuk kenyamanan harian." />
      </div>
      <div class="col-md-4 col-sm-6">
        <x-feature-card title="Jernih / Night Vision" desc="Tetap jelas saat malam dan minim distorsi." />
      </div>
      <div class="col-md-4 col-sm-6">
        <x-feature-card title="Tidak Mengganggu Sinyal" desc="Nyaman untuk GPS, e-toll, dan perangkat." />
      </div>
      <div class="col-md-4 col-sm-6">
        <x-feature-card title="Konsultasi & After Sales" desc="Dibantu pilih tingkat kegelapan & kebutuhan." />
      </div>
      <div class="col-md-4 col-sm-6">
        <x-feature-card title="Pemasangan Profesional" desc="Dikerjakan teknisi berpengalaman & rapi." />
      </div>
    </div>
  </div>
</section>


<section id="gallery" class="section">
  <div class="container">
    <x-section-heading title="Pemasangan Kaca Film Acegard" subtitle="Contoh hasil pemasangan mobil & lokasi." />


    <div class="row g-3">
      @for($i=1;$i<=6;$i++)
        <div class="col-6 col-md-4">
          <img class="img-fluid rounded-4 shadow-sm"
               src="{{ asset("assets/img/gallery-$i.jpg") }}"
               alt="Gallery {{ $i }}">
        </div>
      @endfor
    </div>
  </div>
</section>


<section id="partner" class="section section-soft">
  <div class="container">
    <x-section-heading title="We Work With Different Partner" subtitle="Contoh partner / brand yang umum." />

    <x-partner-logos :logos="[
      ['src'=>'assets/img/partners/toyota.svg','alt'=>'Toyota'],
      ['src'=>'assets/img/partners/honda.svg','alt'=>'Honda'],
      ['src'=>'assets/img/partners/bmw.svg','alt'=>'BMW'],
      ['src'=>'assets/img/partners/mercedes.svg','alt'=>'Mercedes'],
      ['src'=>'assets/img/partners/byd.svg','alt'=>'BYD'],
    ]" />
  </div>
</section>


<section id="contact" class="section">
  <div class="container">
    <div class="row g-4 align-items-start">
      <div class="col-lg-6">
        <h2 class="fw-bold mb-2">Ketik Pesanmu Sekarang</h2>
        <p class="text-secondary">
          Isi form untuk konsultasi. Atau langsung chat WhatsApp untuk respon lebih cepat.
        </p>

        <div class="d-flex gap-2 flex-wrap">
          <a class="btn btn-success btn-lg whatsapp-btn"
             target="_blank"
             href="https://wa.me/6281234567890?text=Halo%20Acegard,%20saya%20mau%20konsultasi%20kaca%20film">
            Chat WhatsApp
          </a>
          <a class="btn btn-outline-primary btn-lg" href="{{ route('warranty.form') }}">
            Warranty Claim
          </a>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="card card-feature p-4">
          <form>
            <div class="mb-3">
              <label class="form-label">Nama</label>
              <input type="text" class="form-control" placeholder="Nama lengkap">
            </div>
            <div class="mb-3">
              <label class="form-label">No. WhatsApp</label>
              <input type="text" class="form-control" placeholder="08xx...">
            </div>
            <div class="mb-3">
              <label class="form-label">Pesan</label>
              <textarea class="form-control" rows="4" placeholder="Saya mau pasang untuk..."></textarea>
            </div>
            <button type="button" class="btn btn-brand w-100">Kirim</button>
            <div class="small text-secondary mt-2">
              *Form demo (belum tersambung).
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="mt-5">
      <h4 class="fw-bold mb-3">Temukan Kami di Google Maps</h4>
      <div class="rounded-4 overflow-hidden shadow-sm">
        <iframe
          src="https://www.google.com/maps?q=Kota%20Tangerang&output=embed"
          width="100%" height="340" style="border:0;" loading="lazy"></iframe>
      </div>
    </div>
  </div>
</section>

@endsection --}}
