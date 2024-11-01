<?php

class TarotReadingAdmin {
	private static $initiated = false;

	public static function init() {
		if (!self::$initiated) {
			self::init_hooks();
		}
	}

	private static function init_hooks() {
		self::$initiated = true;

		// menu
		add_action('admin_menu', function() {
			add_options_page('タロット占い', 'タロット占い', 'manage_options', 'tarot_reading_admin', function() {
				?>
				<div id="tarot_reading_screen1" style="display: none">
					<div class="explanation1">
						<h2>タロット占い設定</h2>
						<p>テーマを追加するとショートコードが発行され、ショートコードをブログ記事内に記載するとタロットカード占いを埋め込む事ができます。
						<br>結果ページを作成し、紐づくテーマをクリックすると結果ページの設定を行う事が可能です。</p>
					</div>
					<h3>テーマ追加</h3>
					<p>
						<input id="tarot_reading_new_theme_name" type="text" style="width: 40em" value="">
						<button id="tarot_reading_add" class="button button-primary">追加</button>
					</p>
					<h3>テーマ一覧</h3>
					<table id="tarot_reading_themes" class="tarot_reading_themes_style">
						<tr id="tarot_reading_template_row" style="display: none">
							<td class="tarot_reading_theme_name tarot_reading_clickable"></td>
							<td class="tarot_reading_shortcode"></td>
							<td><button class="button button-primary tarot_reading_delete">削除</button></td>
						</tr>
						<tr id="tarot_reading_theme_empty" style="display: none;">
							<td colspan="3" style="width: 40em;">テーマが登録されていません。</td>
						</tr>
					</table>
				</div>
				<div id="tarot_reading_screen2" style="display: none">
					<h2>タロット占い設定</h2>
					<p>作成した結果ページのURLを設定してください。（複数ページある場合は改行を設定してください）</p>
					<h3>選択中テーマ</h3>
					<table class="tarot_reading_themes_style">
						<tr>
							<td id="tarot_reading_selected_name" class="tarot_reading_theme_name"></td>
							<td id="tarot_reading_selected_shortcode"></td>
						</tr>
					</table>
					<h3>結果ページ（遷移先URL）</h3>
					<textarea id="tarot_reading_selected_urls" cols="100" rows="20"></textarea>
					<p><button id="tarot_reading_cancel" class="button button-primary">戻る</button>　<button id="tarot_reading_save_urls" class="button button-primary">登録</button></p>
				</div>

				<form id="tarot_reading_form" method="post" action="options.php">
					<?php
					settings_fields('tarot_reading_settings');
					do_settings_sections('tarot_reading_settings');
					?>
				</form>
				<style type="text/css">
					.tarot_reading_themes_style {
						border: 1px solid #333;
						border-spacing: 0;
						border-collapse: collapse;
						background-color: white;
					}
					.tarot_reading_themes_style td {
						border: 1px solid #333;
						padding: 10px;
					}
					td.tarot_reading_theme_name {
						min-width: 40em;
					}
					td.tarot_reading_clickable {
						text-decoration: underline;
						color: #6B98EF;
						cursor: pointer;
					}
				</style>
				<script>
					/*
						[
							{
								"name": "テーマ名",
								"urls": "",
								"shortcode": "1",
							}
						]
					*/
					(function() {
						var onContentLoaded = function() {
							let themeNameEl = document.getElementById('tarot_reading_new_theme_name');
							let buttonEl = document.getElementById("tarot_reading_add");
							let emptyEl = document.getElementById('tarot_reading_theme_empty');
							let valueEl = document.getElementById("tarot_reading_option_value");
							let formEl = document.getElementById('tarot_reading_form');
							let jsonStr = valueEl.value;
							let option = [];
							let maxShortcode = 0;
							if (jsonStr == '') {
								emptyEl.style.display = 'table-row';
							} else {
								let tbodyEl = document.querySelector('#tarot_reading_themes > tbody');
								option = JSON.parse(jsonStr);
								if (option.length == 0) {
									emptyEl.style.display = 'table-row';
								} else {
									let templateEl = document.getElementById('tarot_reading_template_row');
									let clonedEl = templateEl.cloneNode(true);
									clonedEl.style.display = 'table-row';
									clonedEl.id = '';
									for (let i = 0; i < option.length; i++) {
										let obj = option[i];
										let newRow = clonedEl.cloneNode(true);
										newRow.dataset.shortcode = obj.shortcode;
										newRow.querySelector('.tarot_reading_theme_name').textContent = obj.name;
										newRow.querySelector('.tarot_reading_shortcode').textContent = '[tarot_reading name=' + obj.shortcode + ']';
										tbodyEl.appendChild(newRow);
										let sc = parseInt(obj.shortcode, 10);
										maxShortcode = Math.max(sc, maxShortcode);
									}
								}
							}
							buttonEl.addEventListener('click', function() {
								if (themeNameEl.value.trim() == '') {
									alert('テーマを入力してください。');
									return;
								}
								buttonEl.disbaled = true;
								maxShortcode++;
								let obj = {
									"name": themeNameEl.value,
									"urls": "",
									"shortcode": "" + maxShortcode,
								};
								option.push(obj);
								valueEl.value = JSON.stringify(option);
								formEl.submit();
							});
							let deleteEls = document.querySelectorAll('.tarot_reading_delete');
							for (let i = 0; i < deleteEls.length; i++) {
								deleteEls[i].addEventListener('click', function(ev) {
									if (!confirm("削除します。よろしいですか？")) {
										return;
									}
									let trEl = ev.target.closest('tr');
									let sc = trEl.dataset.shortcode;
									for (let i = 0; i < option.length; i++) {
										if (option[i].shortcode == sc) {
											option.splice(i, 1);
											break;
										}
									}
									valueEl.value = JSON.stringify(option);
									formEl.submit();
								});
							}
							let selectedNameEl = document.getElementById('tarot_reading_selected_name');
							let selectedScEl = document.getElementById('tarot_reading_selected_shortcode');
							let selectedUrlsEl = document.getElementById('tarot_reading_selected_urls');
							let selectedOp = null;
							let themeNameEls = document.querySelectorAll('.tarot_reading_theme_name');
							for (let i = 0; i < themeNameEls.length; i++) {
								themeNameEls[i].addEventListener('click', function(ev) {
									let trEl = ev.target.closest('tr');
									let sc = trEl.dataset.shortcode;
									selectedOp = null;
									for (let i = 0; i < option.length; i++) {
										if (option[i].shortcode == sc) {
											selectedOp = option[i];
											break;
										}
									}
									if (selectedOp == null) {
										return;
									}
									selectedNameEl.textContent = selectedOp.name;
									selectedScEl.textContent = '[tarot_reading name=' + selectedOp.shortcode + ']';
									selectedUrlsEl.textContent = selectedOp.urls;
									document.getElementById('tarot_reading_screen1').style.display = 'none';
									document.getElementById('tarot_reading_screen2').style.display = 'block';
								});
							}
							document.getElementById('tarot_reading_save_urls').addEventListener('click', function() {
								selectedOp.urls = selectedUrlsEl.value;
								valueEl.value = JSON.stringify(option);
								formEl.submit();
							});
							document.getElementById('tarot_reading_cancel').addEventListener('click', function() {
								document.getElementById('tarot_reading_screen2').style.display = 'none';
								document.getElementById('tarot_reading_screen1').style.display = 'block';
							});
							document.getElementById('tarot_reading_screen1').style.display = 'block';
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
				</script>
				<?php
			});
		});

		// option page
		add_action('admin_init', function() {
			register_setting('tarot_reading_settings', 'tarot_reading', function($settings) {
				return $settings;
			});
			add_settings_section('tarot_reading_settings_section', null, null, 'tarot_reading_settings');
			add_settings_field('tarot_reading', '', function() {
				?>
				<input type="hidden" name="tarot_reading" id="tarot_reading_option_value" value="<?= htmlspecialchars(get_option('tarot_reading')); ?>">
				<?php
			}, 'tarot_reading_settings', 'tarot_reading_settings_section');
		});
	}
}
