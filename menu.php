<?php
session_start();
require_once __DIR__ . '/includes/avatar_helper.php';

$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$currentAvatar = $user ? getUserAvatar($user['email']) : 'img/converted_image.png';
$avatarTimestamp = isset($_SESSION['user']['selected_avatar_index']) ? $_SESSION['user']['selected_avatar_index'] : (isset($_SESSION['user']['avatar_path']) ? time() : 1);
?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Menu - Martabak Raj'an</title>
	<link rel="stylesheet" href="styles.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
	<style>
		.menu-hero { padding: 18px 24px; display:flex; gap:12px; align-items:center; background: white; border-bottom:1px solid #eee; }
		.filters { display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
		.filter { background:#fff; border:1px solid #eee; padding:8px 12px; border-radius:8px; display:flex; gap:8px; align-items:center; cursor:pointer; }
		.filter select { border: none; background: transparent; outline: none; }
		.search-btn { background:#ff9b2f; color:white; padding:10px 18px; border-radius:8px; border:none; cursor:pointer; font-weight:700; }
			.pill-buy { background:#20a86b; color:#fff; border-radius:8px; padding:8px 12px; cursor:pointer; border:1px solid rgba(32,168,107,0.12); font-weight:700; }
		.cards { padding:24px; display:grid; grid-template-columns: repeat(auto-fill, minmax(520px, 1fr)); gap:20px; }
		.card { background:white; border-radius:12px; padding:16px; display:flex; gap:16px; align-items:center; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
		.card img { width:140px; height:120px; object-fit:cover; border-radius:8px; }
		.card-info { flex:1; }
		.card-title { font-weight:800; margin:0 0 6px 0; }
		.card-sub { color:#777; margin:0 0 8px 0; }
		.order-pills { display:flex; gap:8px; margin-top:8px; flex-wrap:wrap; }
		.pill { background:#fff0e6; color:#ff8c00; border-radius:8px; padding:8px 12px; cursor:pointer; border:1px solid rgba(255,140,0,0.12); font-weight:700; }
		@media (max-width:720px){ .cards { grid-template-columns:1fr } .card img{width:110px;height:90px} }
	</style>
</head>
<body>
	<nav class="navbar-rajan" style="background:white;box-shadow:0 1px 6px rgba(0,0,0,0.04);">
		<div class="navbar-left">
			<img src="img/Cokelat Krem Ilustrasi Imut Logo Martabak Manis (3).png" alt="Logo" class="logo-img">
			<span class="welcome-text"><b>Martabak Raj'an</b></span>
		</div>
		<div class="navbar-right">
			<a href="index.php" class="nav-link">Home</a>
			<a href="contact.php" class="nav-link">Contact</a>
			<?php if ($user): ?>
				<a href="profile.php" class="nav-link">Profile</a>
				<img src="<?php echo htmlspecialchars($currentAvatar); ?>?v=<?php echo $avatarTimestamp; ?>" alt="Avatar" class="avatar" style="width:36px;height:36px;border-radius:50%;object-fit:cover;">
			<?php else: ?>
				<a href="login.php" class="nav-link">Sign In</a>
			<?php endif; ?>
		</div>
	</nav>

	<header class="menu-hero">
			<div class="filters">
			<div class="filter"><i class="fa fa-list"></i>
				<select id="filter-kategori"><option value="">Kategori</option>
					<option value="paket-keluarga">Paket Keluarga</option>
					<option value="paket-romantic">Paket Romantic</option>
					<option value="paket-promo">Paket 1 Free 1</option>
					<option value="paket-hemat">Paket Hemat</option>
					<option value="paket-mini">Paket Mini</option>
					<option value="martabak-marsmelow">Martabak Maramelow</option>
					<option value="martabak-coconut">Coconut Green</option>
					<option value="martabak-kacang">Martabak Kacang</option>
					<option value="martabak-Coklat">Martabak Coklat</option>
					<option value="martabak-keju">Martabak Keju</option>
					<option value="martabak-matcha">Martabak Matcha</option>
				</select>
			</div>
			<div class="filter"><i class="fa fa-pepper-hot"></i>
				<select id="filter-rasa">
					<option value="">Rasa</option>
					<option value="chess">Chess</option>
					<option value="matcha">Matcha</option>
					<option value="oreo">Oreo</option>
					<option value="nuthela">Nuthela</option>
					<option value="mozarella">Mozarella</option>
					<option value="coklat">Coklat</option>
					<option value="marsmelow">Marsmelow</option>
					<option value="peanut">Peanut</option>
					<option value="corn">Corn</option>
					<option value="banana">Banana</option>
					<option value="lainnya">Lain-lain</option>
				</select>
			</div>
			<button class="search-btn" onclick="searchRestaurants()">Search Restaurants For You</button>
		</div>
		<div style="margin-left:auto">
			<button class="filter" onclick="openFilters()">Select Filters <i class="fa fa-filter" style="margin-left:8px;color:#ff8c00"></i></button>
		</div>
	</header>

	<main>
		<section style="padding:18px 24px;">
			<h3 style="margin:0 0 12px 0;">Martabak Raj'an Menu</h3>
			<div class="cards">
				<!-- Card 1 -->
				<div class="card" data-rasa="coklat mozarella peanut" data-kategori="paket-keluarga">
					<div class="card-info">
						<h4 class="card-title">Paket Keluarga</h4>
						<p class="card-sub">Martabak Complete &middot; Special Offer 10% Off</p>
						<div style="color:#ffb366; margin-bottom:6px;"><i class="fas fa-star"></i> <i class="fas fa-star"></i> <i class="fas fa-star"></i></div>
						<div class="order-pills">
							<div class="pill" onclick="orderNow('paket-keluarga','img/placeholder.jpg',1)">1 People</div>
							<div class="pill" onclick="orderNow('paket-keluarga','img/placeholder.jpg',2)">2 People</div>
							<div class="pill" onclick="orderNow('paket-keluarga','img/placeholder.jpg',3)">3 People</div>
							<div class="pill" onclick="orderNow('paket-keluarga','img/placeholder.jpg',4)">Family</div>
							<div class="pill-buy" onclick="buyNow('paket-keluarga')">Beli</div>
						</div>
					</div>
					<img src="img/paket keluarga.jpeg" alt="Paket Keluarga" onerror="this.src='img/placeholder.jpg'">
				</div>

				<!-- Card 2 -->
				<div class="card" data-rasa="marsmelow coklat" data-kategori="martabak-marsmelow">
					<div class="card-info">
						<h4 class="card-title">Martabak Maramelow</h4>
						<p class="card-sub">Sweet & creamy martabak</p>
						<div style="color:#ffb366; margin-bottom:6px;"><i class="fas fa-star"></i> <i class="fas fa-star"></i> <i class="fas fa-star"></i></div>
						<div class="order-pills">
							<div class="pill" onclick="orderNow('martabak-marsmelow','img/mars.jpeg',1)">1 People</div>
							<div class="pill" onclick="orderNow('martabak-marsmelow','img/mars.jpeg',2)">2 People</div>
							<div class="pill" onclick="orderNow('martabak-marsmelow','img/mars.jpeg',3)">3 People</div>
							<div class="pill" onclick="orderNow('martabak-marsmelow','img/mars.jpeg',4)">Family</div>
							<div class="pill-buy" onclick="buyNow('martabak-marsmelow')">Beli</div>
						</div>
					</div>
					<img src="img/mars.jpeg" alt="Martabak Maramelow" onerror="this.src='img/placeholder.jpg'">
				</div>

				<!-- Card 3 -->
				<div class="card" data-rasa="lainnya" data-kategori="martabak-coconut">
					<div class="card-info">
						<h4 class="card-title">Coconut Green Martabak</h4>
						<p class="card-sub">Tropical coconut flavor</p>
						<div style="color:#ffb366; margin-bottom:6px;"><i class="fas fa-star"></i> <i class="fas fa-star"></i> <i class="fas fa-star"></i></div>
						<div class="order-pills">
							<div class="pill" onclick="orderNow('martabak-coconut','img/cg.jpg',1)">1 People</div>
							<div class="pill" onclick="orderNow('martabak-coconut','img/cg.jpg',2)">2 People</div>
							<div class="pill" onclick="orderNow('martabak-coconut','img/cg.jpg',3)">3 People</div>
							<div class="pill" onclick="orderNow('martabak-coconut','img/cg.jpg',4)">Family</div>
							<div class="pill-buy" onclick="buyNow('martabak-coconut')">Beli</div>
						</div>
					</div>
					<img src="img/cg.jpg" alt="Coconut Green" onerror="this.src='img/placeholder.jpg'">
				</div>

				<!-- Card 4 -->
				<div class="card" data-rasa="peanut coklat" data-kategori="martabak-kacang">
					<div class="card-info">
						<h4 class="card-title">Martabak Kacang</h4>
						<p class="card-sub">Crunchy peanut topping</p>
						<div style="color:#ffb366; margin-bottom:6px;"><i class="fas fa-star"></i> <i class="fas fa-star"></i> <i class="fas fa-star"></i></div>
						<div class="order-pills">
							<div class="pill" onclick="orderNow('martabak-kacang','img/kacang.jpg',1)">1 People</div>
							<div class="pill" onclick="orderNow('martabak-kacang','img/kacang.jpg',2)">2 People</div>
							<div class="pill" onclick="orderNow('martabak-kacang','img/kacang.jpg',3)">3 People</div>
							<div class="pill" onclick="orderNow('martabak-kacang','img/kacang.jpg',4)">Family</div>
							<div class="pill-buy" onclick="buyNow('martabak-kacang')">Beli</div>
						</div>
					</div>
					<img src="img/kacang.jpg" alt="Martabak Kacang" onerror="this.src='img/placeholder.jpg'">
				</div>

				<!-- Additional Packages -->
				<div class="card" data-rasa="coklat mozarella" data-kategori="paket-romantic">
					<div class="card-info">
						<h4 class="card-title">Paket Romantic</h4>
						<p class="card-sub">Pilihan manis untuk pasangan — Free topping</p>
						<div style="color:#ffb366; margin-bottom:6px;"><i class="fas fa-star"></i> <i class="fas fa-star"></i></div>
						<div class="order-pills">
							<div class="pill" onclick="orderNow('paket-romantic','img/paket bucin.jpeg',2)">2 People</div>
							<div class="pill" onclick="orderNow('paket-romantic','img/paket bucin.jpeg',4)">Family</div>
							<div class="pill-buy" onclick="buyNow('paket-romantic')">Beli</div>
						</div>
					</div>
					<img src="img/paket bucin.jpeg" alt="Paket Romantic" onerror="this.src='img/placeholder.jpg'">
				</div>

				<div class="card" data-rasa="oreo coklat" data-kategori="paket-promo">
					<div class="card-info">
						<h4 class="card-title">Paket 1 Free 1</h4>
						<p class="card-sub">Diskon spesial hari ini — Hemat 15%</p>
						<div style="color:#ffb366; margin-bottom:6px;"><i class="fas fa-star"></i> <i class="fas fa-star"></i> <i class="fas fa-star"></i></div>
						<div class="order-pills">
							<div class="pill" onclick="orderNow('paket-promo','img/paket 1 - 1.jpg',1)">1 People</div>
							<div class="pill" onclick="orderNow('paket-promo','img/paket 1 - 1.jpg',2)">2 People</div>
							<div class="pill-buy" onclick="buyNow('paket-promo')">Beli</div>
						</div>
					</div>
					<img src="img/paket 1 - 1.jpg" alt="Paket Promo" onerror="this.src='img/placeholder.jpg'">
				</div>

				<div class="card" data-rasa="coklat banana" data-kategori="paket-hemat">
					<div class="card-info">
						<h4 class="card-title">Paket Hemat</h4>
						<p class="card-sub">Pilihan ekonomis untuk keluarga kecil</p>
						<div style="color:#ffb366; margin-bottom:6px;"><i class="fas fa-star"></i></div>
						<div class="order-pills">
							<div class="pill" onclick="orderNow('paket-hemat','img/Hemat.jpeg',2)">2 People</div>
							<div class="pill" onclick="orderNow('paket-hemat','img/Hemat.jpeg',3)">3 People</div>
							<div class="pill-buy" onclick="buyNow('paket-hemat')">Beli</div>
						</div>
					</div>
					<img src="img/Hemat.jpeg" alt="Paket Hemat" onerror="this.src='img/placeholder.jpg'">
				</div>

				<div class="card" data-rasa="coklat oreo" data-kategori="paket-mini">
					<div class="card-info">
						<h4 class="card-title">Paket Mini</h4>
						<p class="card-sub">Porsi kecil, pas untuk cemilan</p>
						<div style="color:#ffb366; margin-bottom:6px;"><i class="fas fa-star"></i></div>
						<div class="order-pills">
							<div class="pill" onclick="orderNow('paket-mini','img/mini.png',1)">1 People</div>
							<div class="pill-buy" onclick="buyNow('paket-mini')">Beli</div>
						</div>
					</div>
					<img src="img/mini.png" alt="Paket Mini" onerror="this.src='img/placeholder.jpg'">
				</div>
				<div class="card" data-rasa="coklat " data-kategori="Martabak-Coklat">
					<div class="card-info">
						<h4 class="card-title">Martabak Coklat</h4>
						<p class="card-sub">Coklat yang lezat dan creamy</p>
						<div style="color:#ffb366; margin-bottom:6px;"><i class="fas fa-star"></i></div>
						<div class="order-pills">
							<div class="pill-buy" onclick="buyNow('paket-mini')">Beli</div>
						</div>
					</div>
					<img src="img/coklat.jpg" alt="Paket Mini" onerror="this.src='img/placeholder.jpg'">
				</div>
				<div class="card" data-rasa="coklat mozarella" data-kategori="paket-romantic">
					<div class="card-info">
						<h4 class="card-title">Paket Romantic</h4>
						<p class="card-sub">Pilihan untuk hati tersayang ku — Free topping</p>
						<div style="color:#ffb366; margin-bottom:6px;"><i class="fas fa-star"></i> <i class="fas fa-star"></i></div>
						<div class="order-pills">
							<div class="pill-buy" onclick="buyNow('paket-romantic')">Beli</div>
						</div>
					</div>
					<img src="img/Bucin.webp" alt="Martabk keju" onerror="this.src='img/placeholder.jpg'">
				</div>
				<div class="card" data-rasa="Chess" data-kategori="Martabak-keju">
					<div class="card-info">
						<h4 class="card-title">Martabak keju</h4>
						<p class="card-sub">keju yang lezat dan creamy</p>
						<div style="color:#ffb366; margin-bottom:6px;"><i class="fas fa-star"></i></div>
						<div class="order-pills">
							<div class="pill-buy" onclick="buyNow('paket-mini')">Beli</div>
						</div>
					</div>
					<img src="img/Keju1.jpg" alt="Martabk keju" onerror="this.src='img/placeholder.jpg'">
			</div>
					<div class="card" data-rasa="matcha " data-kategori="martabak-matcha">
					<div class="card-info">
						<h4 class="card-title">Martabak Matcha</h4>
						<p class="card-sub">Matcha enak</p>
						<div style="color:#ffb366; margin-bottom:6px;"><i class="fas fa-star"></i> <i class="fas fa-star"></i> <i class="fas fa-star"></i></div>
						<div class="order-pills">
							<div class="pill-buy" onclick="buyNow('martabak-coconut')">Beli</div>
						</div>
					</div>
					<img src="img/Mmatcha.jpeg" alt="Coconut Green" onerror="this.src='img/placeholder.jpg'">
				</div>
			</div>
		</section>
	</main>

	<script>
		function openFilters(){ alert('Filter panel (placeholder)'); }
		function searchRestaurants(){ alert('Searching (placeholder)'); }
		function orderNow(menuSlug, menuImage, people){
			// go to order.php with parameters so the order page can prefill
			let url = 'order.php?menu=' + encodeURIComponent(menuSlug) + '&menu_image=' + encodeURIComponent(menuImage) + '&people=' + encodeURIComponent(people);
			window.location.href = url;
		}

		// Flavor filter: show only cards that list the selected flavor in their data-rasa
		(function(){
			const rasaSelect = document.getElementById('filter-rasa');
			const kategoriSelect = document.getElementById('filter-kategori');
			const cards = Array.from(document.querySelectorAll('.cards .card'));

			function matchesToken(listStr, token){
				if(!token) return true;
				const list = (listStr || '').toLowerCase().split(/\s+/).filter(Boolean);
				token = token.toLowerCase();
				return list.indexOf(token) !== -1 || list.join(' ').indexOf(token) !== -1;
			}

			function applyFilters(){
				const rasaVal = (rasaSelect && rasaSelect.value) ? rasaSelect.value.trim().toLowerCase() : '';
				const katVal = (kategoriSelect && kategoriSelect.value) ? kategoriSelect.value.trim().toLowerCase() : '';

				cards.forEach(c => {
					const dataRasa = (c.getAttribute('data-rasa') || '').toLowerCase();
					const dataKat = (c.getAttribute('data-kategori') || '').toLowerCase();

					const rasaOk = rasaVal ? matchesToken(dataRasa, rasaVal) : true;
					const katOk = katVal ? matchesToken(dataKat, katVal) : true;

					c.style.display = (rasaOk && katOk) ? 'flex' : 'none';
				});
			}

			if(rasaSelect) rasaSelect.addEventListener('change', applyFilters);
			if(kategoriSelect) kategoriSelect.addEventListener('change', applyFilters);
			window.addEventListener('DOMContentLoaded', applyFilters);
		})();

		// Navigate to payment page with selected menu slug
		function buyNow(menuSlug){
			if(!menuSlug) return window.location.href = 'payment.php';
			const url = 'payment.php?menu=' + encodeURIComponent(menuSlug);
			window.location.href = url;
		}
	</script>
</body>
</html>
