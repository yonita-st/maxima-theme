/* ============================================================
 * БЛОК 4.3 – Daily Interactive Checklist (JS част)
 * ------------------------------------------------------------
 * Подблок 4.3.1 – Атрибути и регистрация
 * Подблок 4.3.2 – Редакторски интерфейс (edit)
 * Подблок 4.3.3 – Запис (save) – минимален markup + data-*
 * Подблок 4.3.4 – Фронтенд: рендер + AJAX load/save
 * Подблок 4.3.5 – Стилове (инжектирани)
 * ============================================================ */

( function( wp, $ ) {
	const { registerBlockType } = wp.blocks;
	const { RichText, useBlockProps } = wp.blockEditor;

	/* ---------- Подблок 4.3.1 – Атрибути и регистрация ---------- */
	registerBlockType('mx/daily-interactive-checklist', {
		title: 'Daily Interactive Checklist',
		icon: 'yes',
		category: 'widgets',
		attributes: {
			uid: { type: 'string' },
			leftTitle: { type: 'string', default: 'Какво да направя днес:' },
			rightTitle: { type: 'string', default: 'Какво да не правя днес:' },
			leftItems: { type: 'array', default: [] },   // [{rowId,text}]
			rightItems:{ type: 'array', default: [] }
		},
/* ---------- Подблок 4.3.2 – Редакторски интерфейс (edit) ---------- */
edit: ( props ) => {
	const { attributes, setAttributes } = props;

	// Генериране на глобално уникален uid (еднократно)
	if ( ! attributes.uid ) {
		setAttributes({ uid: 'dic-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2,6) });
	}

	// Инициализация на масивите, ако липсват
	if (!Array.isArray(attributes.leftItems))  setAttributes({ leftItems: [] });
	if (!Array.isArray(attributes.rightItems)) setAttributes({ rightItems: [] });

	// Помощна функция за избор на правилния ключ
	const keyFor = (side) => side === 'left' ? 'leftItems' : 'rightItems';

	const updateItem = (side, index, text) => {
		const k = keyFor(side);
		const items = [ ...(attributes[k] || []) ];
		items[index].text = text;
		setAttributes({ [k]: items });
	};

	const addItem = (side) => {
		const k = keyFor(side);
		const items = [ ...(attributes[k] || []) ];
		items.push({ rowId: 'r-' + Math.random().toString(36).slice(2,6), text: '' });
		setAttributes({ [k]: items });
	};

	const deleteItem = (side, index) => {
		const k = keyFor(side);
		const items = [ ...(attributes[k] || []) ];
		items.splice(index,1);
		setAttributes({ [k]: items });
	};

	const moveItem = (side, index, dir) => {
		const k = keyFor(side);
		const items = [ ...(attributes[k] || []) ];
		const ni = index + dir;
		if (ni < 0 || ni >= items.length) return;
		[items[index], items[ni]] = [items[ni], items[index]];
		setAttributes({ [k]: items });
	};

	const renderColumn = (side, titleAttr) => {
		const k = keyFor(side);
		return wp.element.createElement('div', { className: 'dic-col dic-' + side },
			wp.element.createElement(wp.blockEditor.RichText, {
				tagName: 'h3',
				value: attributes[titleAttr],
				onChange: (val) => setAttributes({ [titleAttr]: val })
			}),
			(attributes[k] || []).map((item, i) =>
				wp.element.createElement('div', { className: 'dic-row', key: item.rowId },
					wp.element.createElement(wp.blockEditor.RichText, {
						tagName: 'p',
						value: item.text,
						onChange: (val)=> updateItem(side, i, val),
						placeholder: 'Задача…'
					}),
					wp.element.createElement('div', { className: 'dic-row-tools' },
						wp.element.createElement(wp.components.Button, { onClick:()=>moveItem(side,i,-1), isSmall:true }, '↑'),
						wp.element.createElement(wp.components.Button, { onClick:()=>moveItem(side,i, 1), isSmall:true }, '↓'),
						wp.element.createElement(wp.components.Button, { onClick:()=>deleteItem(side,i), isDestructive:true, isSmall:true }, '✕')
					)
				)
			),
			wp.element.createElement(
				wp.components.Button,
				{ isPrimary: true, onClick: ()=>addItem(side), className: 'dic-add' },
				'+ Добави ред'
			)
		);
	};

	return wp.element.createElement('div', wp.blockEditor.useBlockProps({ className: 'dic-wrap dic-editor' }),
		renderColumn('left','leftTitle'),
		renderColumn('right','rightTitle')
	);
},

		/* ---------- Подблок 4.3.3 – Запис (save) ---------- */
		save: ( props ) => {
			const { attributes } = props;
			return wp.element.createElement('div', {
				className: 'dic-wrap',
				'data-uid': attributes.uid || '',
				'data-left': JSON.stringify(attributes.leftItems || []),
				'data-right': JSON.stringify(attributes.rightItems || []),
				'data-lefttitle': attributes.leftTitle || '',
				'data-righttitle': attributes.rightTitle || ''
			});
		}
	});

	/* ---------- Подблок 4.3.4 – Фронтенд: рендер + AJAX load/save ---------- */
	jQuery(function($){
		$('.dic-wrap').each(function(){
			const $wrap = $(this);
			if ($wrap.hasClass('dic-editor')) return; // в редактора не изпълняваме фронтенд логика

			const uid = String($wrap.data('uid') || '');
			const leftItems  = JSON.parse($wrap.attr('data-left')  || '[]');
			const rightItems = JSON.parse($wrap.attr('data-right') || '[]');
			const leftTitle  = String($wrap.attr('data-lefttitle')  || '');
			const rightTitle = String($wrap.attr('data-righttitle') || '');

			const makeCol = (side, items, title, cls) => {
				let html = `<div class="dic-col dic-${cls}"><h3>${title}</h3>`;
				items.forEach(it => {
					const rid = it.rowId;
					const text = it.text || '';
					const inputId = `${uid}-${rid}`;
					html += `<label class="dic-check">
						<input type="checkbox" id="${inputId}" data-side="${side}" data-id="${rid}">
						<span class="dic-box"></span>
						<span class="dic-text">${text}</span>
					</label>`;
				});
				html += `</div>`;
				return html;
			};

			$wrap.html(
				makeCol('left', leftItems, leftTitle, 'left') +
				makeCol('right', rightItems, rightTitle, 'right')
			);

			// Load
			$.post(MX_DIC.ajaxurl, {
				action:'mx_dic_load',
				nonce: MX_DIC.nonce,
				uid: uid
			}).done(function(res){
				if(res && res.success && res.data){
					['left','right'].forEach(side=>{
						(res.data[side] || []).forEach(rowId=>{
							$wrap.find(`input[data-side="${side}"][data-id="${rowId}"]`).prop('checked', true);
						});
					});
				}
			});

			// Save on change
			const saveState = () => {
				const data = { left:[], right:[] };
				$wrap.find('input[data-side="left"]:checked').each(function(){ data.left.push($(this).data('id')); });
				$wrap.find('input[data-side="right"]:checked').each(function(){ data.right.push($(this).data('id')); });
				$.post(MX_DIC.ajaxurl, {
					action:'mx_dic_save',
					nonce: MX_DIC.nonce,
					uid: uid,
					data: data
				});
			};
			$wrap.on('change', 'input[type="checkbox"]', saveState);
		});
	});

/* ---------- Подблок 4.3.5 – Стилове (инжектирани) ---------- */
const style = document.createElement('style');
style.id = 'mx-dic-styles';
style.innerHTML = `
	.dic-wrap{
  box-sizing:border-box;
  width:100%;
  margin:20px 0;               /* вместо auto → да заеме цялата ширина */
  display:flex; gap:20px;
  padding:20px;
  border-radius:16px;
  backdrop-filter:blur(12px);
  background:rgba(255,255,255,0.50);
  box-shadow:0 8px 24px rgba(0,0,0,0.25);
  font-family:inherit;
}

	.dic-col{flex:1;min-width:0}
	.dic-col h3{margin:0 0 12px 0;padding:10px 14px;color:#fff;border-radius:10px}
	.dic-col.dic-left h3{background:#28a745}
	.dic-col.dic-right h3{background:#de605f}

	/* чекбокси */
	.dic-check{display:flex;align-items:center;gap:10px;padding:8px 6px;border-radius:8px;cursor:pointer;user-select:none}
	.dic-check input{position:absolute;opacity:0;pointer-events:none}
	.dic-box{display:inline-block;width:20px;height:20px;border-radius:4px;position:relative;flex-shrink:0;background:transparent}

	/* неотметнато: само рамка */
	.dic-col.dic-left  .dic-box{border:2px solid #28a745}
	.dic-col.dic-right .dic-box{border:2px solid #de605f}

	/* отметнато: запълнен бокс + бяла отметка */
	.dic-col.dic-left  input:checked + .dic-box{background:#28a745;border-color:#28a745}
	.dic-col.dic-right input:checked + .dic-box{background:#de605f;border-color:#de605f}

	.dic-check input:checked + .dic-box::after{
		content:'✓';position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);
		color:#fff;font-weight:700;font-size:14px;line-height:1;
	}

	.dic-text{flex:1;min-width:0}
	.dic-editor .dic-row{display:flex;align-items:flex-start;gap:8px;margin:6px 0}
	.dic-editor .dic-row .dic-row-tools button{margin-right:6px}
	.dic-editor .dic-add{margin-top:8px}
	@media (max-width: 768px){ .dic-wrap{flex-direction:column} }
`;
if (!document.getElementById('mx-dic-styles')) document.head.appendChild(style);


})( window.wp, jQuery );
