(function() {

	// カードを展開する時間（ミリ秒）
	const cardDistributionTime = 3 * 1000;

	const cardFiles = [
		'tarot4_1.gif',
		'tarot4_2.gif',
		'tarot4_3.gif',
	];

	const jumpUrl = "https://amory.jp/users/lp/01?utm_source=plugin&utm_medium=affiliate&utm_campaign=01";

	var onContentLoaded = function() {
		let pathEls = document.querySelectorAll('.tarot_reading_path');
		if (pathEls == null) {
			return;
		}
		for (let i = 0; i < 3; i++) {
			let images = document.querySelectorAll('.tarot_reading_step' + (i + 1) + ' > img');
			for (let j = 0; j < images.length; j++) {
				images[j].src = pathEls[j].value + cardFiles[i];
			}
		}

		function onTarotShown(entries, observer) {
			for (let i = 0; i < entries.length; i++) {
				let entry = entries[i];
				if (!entry.isIntersecting || tarotShown[entry.target.dataset.index]) {
					continue;
				}
				tarotShown[entry.target.dataset.index] = true;
				// start fade-in
				entry.target.classList.add('tarot_reading_shown');
			}
		}

		// フェードイン
		let parentDiv = document.querySelectorAll('.tarot_reading');
		let tarotShown = new Array(parentDiv.length);
		for (let i = 0; i < parentDiv.length; i++) {
			let d = parentDiv[i];
			d.dataset.index = i;
			if (IntersectionObserver) {
				let observer = new IntersectionObserver(onTarotShown, {
					threshold: 0.5,
				});
				observer.observe(d);
			} else {
				d.style.opacity = 1;
			}
		}

		let jumpLinkEls = document.querySelectorAll('.tarot_reader_icon,.tarot_reader_balloon');
		for (let i = 0; i < jumpLinkEls.length; i++) {
			jumpLinkEls[i].addEventListener('click', function(ev) {
				//window.location.href = jumpUrl;
				let link = document.createElement('A');
				link.setAttribute('href', jumpUrl);
				link.setAttribute('target', '_blank');
				ev.target.closest('div').parentElement.appendChild(link);
				link.click();
			});
		}

		let clickCount = 0;
		let rectEls = document.querySelectorAll('.tarot_reading_rect');
		let shaffleStopped = new Array(rectEls.length);
		for (let i = 0; i < rectEls.length; i++) {
			rectEls[i].addEventListener('click', function(ev) {
				if (clickCount === 0) {
					let curDiv = ev.target.closest('div.tarot_reading_cards').querySelector('div.tarot_reading_step1');
					let nextDiv = curDiv.nextElementSibling;
					curDiv.style.display = 'none';
					nextDiv.style.display = 'block';
					clickCount++;

				} else if (clickCount === 1) {
					let curDiv = ev.target.closest('div.tarot_reading_cards').querySelector('div.tarot_reading_step2');
					let nextDiv = curDiv.nextElementSibling;
					curDiv.style.display = 'none';
					nextDiv.style.display = 'block';
					shaffleStopped[i] = Date.now();
					clickCount++;

				} else if (clickCount >= 2) {
					if (Date.now() < shaffleStopped[i] + cardDistributionTime) {
						// 一定時間はクリックを無視
						return;
					}
					let curDiv = ev.target.closest('div.tarot_reading_cards').querySelector('div.tarot_reading_step3');
					curDiv.parentElement.parentElement.classList.remove('tarot_reading_shown');
					let input = curDiv.parentElement.parentElement.querySelector('input.tarot_reading_url');
					window.setTimeout(function() {
						window.location.href = input.value;
					}, 500);
				}
			});
		}

		// 静止画
		let step1Images = document.querySelectorAll('.tarot_reading_step1 > img');
		for (let i = 0; i < step1Images.length; i++) {
			let img = step1Images[i];
			img.dataset.instance = i;
			// img.addEventListener('click', function(ev) {
			// 	// 静止画: いつでもクリック可能
			// 	let curDiv = ev.target.parentElement;
			// 	let nextDiv = curDiv.nextElementSibling;
			// 	curDiv.style.display = 'none';
			// 	nextDiv.style.display = 'block';
			// });
		}

		// シャッフル
		// let shaffleStopped = 0;
		let step2Images = document.querySelectorAll('.tarot_reading_step2 > img');
		for (let i = 0; i < step2Images.length; i++) {
			// シャッフル: いつでもクリック可能
			let img = step2Images[i];
			img.dataset.instance = i;
			// img.addEventListener('click', function(ev) {
			// 	let curDiv = ev.target.parentElement;
			// 	let nextDiv = curDiv.nextElementSibling;
			// 	curDiv.style.display = 'none';
			// 	nextDiv.style.display = 'block';
			// 	shaffleStopped = Date.now();
			// });
		}

		// カード展開中
		let step3Images = document.querySelectorAll('.tarot_reading_step3 > img');
		for (let i = 0; i < step3Images.length; i++) {
			let img = step3Images[i];
			img.dataset.instance = i;
			// img.addEventListener('click', function(ev) {
			// 	if (Date.now() < shaffleStopped + cardDistributionTime) {
			// 		// 一定時間はクリックを無視
			// 		return;
			// 	}
			// 	let curDiv = ev.target.parentElement;
			// 	ev.target.parentElement.parentElement.parentElement.classList.remove('tarot_reading_shown');
			// 	let input = ev.target.parentElement.parentElement.parentElement.querySelector('input.tarot_reading_url');
			// 	window.setTimeout(function() {
			// 		window.location.href = input.value;
			// 	}, 500);
			// });
		}
	};

	if (
		document.readyState === "complete" ||
		(document.readyState !== "loading"
			&& !document.documentElement.doScroll)
	) {
		onContentLoaded();
	} else {
		document.addEventListener("DOMContentLoaded", onContentLoaded);
	}
}());
