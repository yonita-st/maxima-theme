(function (wp) {
  const { registerBlockType } = wp.blocks;
  const { __ } = wp.i18n;
  const {
    PanelBody, RangeControl, __experimentalNumberControl: NumberControl,
    ToggleControl, ColorPalette, TextControl
  } = wp.components;
  const { InspectorControls, useBlockProps } = wp.blockEditor || wp.editor;
  const { useEffect } = wp.element;
  const el = wp.element.createElement;

  const clamp = (v, min, max) => Math.min(Math.max(Number(v ?? 0), min), max);
  function lighten(hex, amt = 0.40) {
    hex = String(hex || '#de605f').replace('#', '');
    if (hex.length === 3) hex = hex.split('').map(c => c + c).join('');
    const n = parseInt(hex, 16);
    let r = (n >> 16) & 255, g = (n >> 8) & 255, b = n & 255;
    r = Math.round(r + (255 - r) * amt);
    g = Math.round(g + (255 - g) * amt);
    b = Math.round(b + (255 - b) * amt);
    return '#' + [r, g, b].map(x => x.toString(16).padStart(2, '0')).join('');
  }

  function Circle({
    value, max, size, trackThickness, barThickness, baseColor, trackColor, showNumber,
    numberFontSize, numberColor, numberFontFamily, instanceId
  }) {
    max = Math.max(1, max);
    value = clamp(value, 0, max);
    const pct = value / max;

    const cx = size / 2, cy = size / 2;
    const r  = (size / 2) - (Math.max(trackThickness, barThickness) / 2);
    const C = 2 * Math.PI * r;
    const dashOffset = C * (1 - pct);

    // стабилен ID (без разминаване между edit/save)
    const gradId = 'mxGrad_' + (instanceId || '0');

    return el('div', { className: 'mx-progress', style: { width: size, height: size, position: 'relative' } },
      el('svg', { width: size, height: size, viewBox: `0 0 ${size} ${size}` },
        el('defs', null,
          el('linearGradient', { id: gradId, x1: '0%', y1: '0%', x2: '0%', y2: '100%' },
            el('stop', { offset: '0%',  stopColor: lighten(baseColor, 0.40) }),
            el('stop', { offset: '100%', stopColor: baseColor })
          )
        ),
        // СИВ ТРАК – цветът идва като inline style (побеждава външен CSS)
        el('circle', {
          cx, cy, r, fill: 'none',
          'stroke-width': trackThickness,
          'stroke-linecap': 'butt',
          style: { stroke: trackColor || '#E8E8E8' }
        }),
        // ЦВЕТЕН БАР – градиент (stroke атрибут с url)
        el('circle', {
          cx, cy, r, fill: 'none',
          stroke: `url(#${gradId})`,
          'stroke-width': barThickness,
          'stroke-linecap': 'butt',
          'stroke-dasharray': C,
          'stroke-dashoffset': dashOffset,
          transform: `rotate(-90 ${cx} ${cy})`
        })
      ),
      showNumber && el('div', {
        className: 'mx-progress__center',
        style: {
          position: 'absolute', inset: 0, display: 'flex',
          alignItems: 'center', justifyContent: 'center',
          pointerEvents: 'none', fontWeight: 800, lineHeight: 1,
          fontSize: (numberFontSize || 28) + 'px',
          color: numberColor || 'currentColor',
          fontFamily: numberFontFamily || 'Raleway, sans-serif'
        }
      }, el('span', { className: 'mx-progress__number' }, String(value)))
    );
  }

  const DEFAULTS = {
    value: 26, max: 100,
    size: 140,
    trackThickness: 12,
    barThickness: 18,
    baseColor: '#de605f',
    trackColor: '#E8E8E8',
    showNumber: true,
    numberFontSize: 28,
    numberColor: 'currentColor',
    numberFontFamily: 'Raleway, sans-serif',
    instanceId: '' // сетва се веднъж от edit()
  };

  registerBlockType('maxima/progress-circle', {
    apiVersion: 2,
    title: __('Progress Circle', 'maxima'),
    icon: 'chart-pie',
    category: 'widgets',
    attributes: {
      value:            { type: 'number',  default: DEFAULTS.value },
      max:              { type: 'number',  default: DEFAULTS.max },
      size:             { type: 'number',  default: DEFAULTS.size },
      trackThickness:   { type: 'number',  default: DEFAULTS.trackThickness },
      barThickness:     { type: 'number',  default: DEFAULTS.barThickness },
      baseColor:        { type: 'string',  default: DEFAULTS.baseColor },
      trackColor:       { type: 'string',  default: DEFAULTS.trackColor },
      showNumber:       { type: 'boolean', default: DEFAULTS.showNumber },
      numberFontSize:   { type: 'number',  default: DEFAULTS.numberFontSize },
      numberColor:      { type: 'string',  default: DEFAULTS.numberColor },
      numberFontFamily: { type: 'string',  default: DEFAULTS.numberFontFamily },
      instanceId:       { type: 'string',  default: DEFAULTS.instanceId }
    },
    supports: { html: false },

    edit: ({ attributes, setAttributes, clientId }) => {
      const blockProps = useBlockProps({ className: 'mx-progress-wrap' });

      // сетваме стабилен instanceId само веднъж
      useEffect(() => {
        if (!attributes.instanceId) {
          setAttributes({ instanceId: clientId });
        }
      }, [attributes.instanceId, clientId]);

      const {
        value, max, size, trackThickness, barThickness, baseColor, trackColor, showNumber,
        numberFontSize, numberColor, numberFontFamily, instanceId
      } = attributes;

      return el('div', blockProps,
        el(InspectorControls, null,
          el(PanelBody, { title: __('Values', 'maxima'), initialOpen: true },
            el(RangeControl, {
              label: __('Value', 'maxima'), value, min: 0, max: 1000,
              onChange: (v) => setAttributes({ value: clamp(v, 0, 100000) })
            }),
            el(NumberControl, {
              label: __('Max', 'maxima'), value: max,
              onChange: (v) => setAttributes({ max: Math.max(1, Number(v || 1)) })
            }),
            el(ToggleControl, {
              label: __('Show number inside', 'maxima'),
              checked: showNumber, onChange: (b) => setAttributes({ showNumber: b })
            })
          ),
          el(PanelBody, { title: __('Appearance', 'maxima'), initialOpen: false },
            el(NumberControl, {
              label: __('Size (px)', 'maxima'), value: size,
              onChange: (v) => setAttributes({ size: clamp(v, 80, 400) })
            }),
            el(NumberControl, {
              label: __('Track thickness (px)', 'maxima'), value: trackThickness,
              onChange: (v) => setAttributes({ trackThickness: clamp(v, 4, 80) })
            }),
            el(NumberControl, {
              label: __('Bar thickness (px)', 'maxima'), value: barThickness,
              onChange: (v) => setAttributes({ barThickness: clamp(v, 6, 90) })
            }),
            el('div', { style: { marginTop: 8 } }, __('Base color', 'maxima')),
            el(ColorPalette, {
              value: baseColor,
              onChange: (c) => setAttributes({ baseColor: c || DEFAULTS.baseColor })
            }),
            el('div', { style: { marginTop: 8 } }, __('Track color', 'maxima')),
            el(ColorPalette, {
              value: trackColor,
              onChange: (c) => setAttributes({ trackColor: c || DEFAULTS.trackColor })
            })
          ),
          el(PanelBody, { title: __('Number style', 'maxima'), initialOpen: false },
            el(NumberControl, {
              label: __('Font size (px)', 'maxima'),
              value: numberFontSize,
              onChange: (v) => setAttributes({ numberFontSize: clamp(v, 8, 160) })
            }),
            el('div', { style: { marginTop: 8 } }, __('Number color', 'maxima')),
            el(ColorPalette, {
              value: numberColor === 'currentColor' ? undefined : numberColor,
              onChange: (c) => setAttributes({ numberColor: c || 'currentColor' })
            }),
            el(TextControl, {
              label: __('Font family', 'maxima'),
              help: __('Напр. "Raleway, sans-serif" или "inherit"', 'maxima'),
              value: numberFontFamily,
              onChange: (t) => setAttributes({ numberFontFamily: t || 'Raleway, sans-serif' })
            })
          )
        ),
        el(Circle, { ...attributes, instanceId })
      );
    },

    save: ({ attributes }) => {
      const {
        value, max, size, trackThickness, barThickness, baseColor, trackColor, showNumber,
        numberFontSize, numberColor, numberFontFamily, instanceId
      } = attributes;

      const m = Math.max(1, max);
      const v = Math.min(Math.max(0, value), m);
      const pct = v / m;

      const cx = size / 2, cy = size / 2;
      const r  = (size / 2) - (Math.max(trackThickness, barThickness) / 2);
      const C = 2 * Math.PI * r;
      const dashOffset = C * (1 - pct);

      const gradId = 'mxGrad_' + (instanceId || '0');
      const blockProps = (wp.blockEditor
        ? wp.blockEditor.useBlockProps.save({ className: 'mx-progress-wrap' })
        : { className: 'mx-progress-wrap' });

      return el('div', blockProps,
        el('div', { className: 'mx-progress', style: { width: size, height: size, position: 'relative' } },
          el('svg', { width: size, height: size, viewBox: `0 0 ${size} ${size}` },
            el('defs', null,
              el('linearGradient', { id: gradId, x1: '0%', y1: '0%', x2: '0%', y2: '100%' },
                el('stop', { offset: '0%', 'stop-color': lighten(baseColor, 0.40) }),
                el('stop', { offset: '100%', 'stop-color': baseColor })
              )
            ),
            // Тук също подаваме цвета като inline style (детерминистичен HTML)
            el('circle', {
              cx, cy, r, fill: 'none',
              className: 'mx-track',
              'stroke-width': trackThickness,
              'stroke-linecap': 'butt',
              style: { stroke: trackColor || DEFAULTS.trackColor }
            }),
            el('circle', {
              cx, cy, r, fill: 'none',
              className: 'mx-bar',
              'stroke-width': barThickness,
              'stroke-dasharray': C,
              'stroke-dashoffset': dashOffset,
              transform: `rotate(-90 ${cx} ${cy})`,
              stroke: `url(#${gradId})`,
              'stroke-linecap': 'butt'
            })
          ),
          showNumber && el('div', {
            className: 'mx-progress__center',
            style: {
              position: 'absolute', inset: 0, display: 'flex',
              alignItems: 'center', justifyContent: 'center',
              pointerEvents: 'none', fontWeight: 800, lineHeight: 1,
              fontSize: (numberFontSize || 28) + 'px',
              color: numberColor || 'currentColor',
              fontFamily: numberFontFamily || 'Raleway, sans-serif'
            }
          }, el('span', { className: 'mx-progress__number' }, String(value)))
        )
      );
    }
  });
})(window.wp);
