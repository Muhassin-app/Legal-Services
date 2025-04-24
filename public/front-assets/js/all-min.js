/*
 Copyright (C) Federico Zivolo 2017
 Distributed under the MIT License (license terms are at http://opensource.org/licenses/MIT).
 */
 (function(e, t) {
	'object' == typeof exports && 'undefined' != typeof module ? module.exports = t() : 'function' == typeof define && define.amd ? define(t) : e.Popper = t()
})(this, function() {
	'use strict';

	function e(e) {
		return e && '[object Function]' === {}.toString.call(e)
	}

	function t(e, t) {
		if(1 !== e.nodeType) return [];
		var o = window.getComputedStyle(e, null);
		return t ? o[t] : o
	}

	function o(e) {
		return 'HTML' === e.nodeName ? e : e.parentNode || e.host
	}

	function n(e) {
		if(!e || -1 !== ['HTML', 'BODY', '#document'].indexOf(e.nodeName)) return window.document.body;
		var i = t(e),
			r = i.overflow,
			p = i.overflowX,
			s = i.overflowY;
		return /(auto|scroll)/.test(r + s + p) ? e : n(o(e))
	}

	function r(e) {
		var o = e && e.offsetParent,
			i = o && o.nodeName;
		return i && 'BODY' !== i && 'HTML' !== i ? -1 !== ['TD', 'TABLE'].indexOf(o.nodeName) && 'static' === t(o, 'position') ? r(o) : o : window.document.documentElement
	}

	function p(e) {
		var t = e.nodeName;
		return 'BODY' !== t && ('HTML' === t || r(e.firstElementChild) === e)
	}

	function s(e) {
		return null === e.parentNode ? e : s(e.parentNode)
	}

	function d(e, t) {
		if(!e || !e.nodeType || !t || !t.nodeType) return window.document.documentElement;
		var o = e.compareDocumentPosition(t) & Node.DOCUMENT_POSITION_FOLLOWING,
			i = o ? e : t,
			n = o ? t : e,
			a = document.createRange();
		a.setStart(i, 0), a.setEnd(n, 0);
		var l = a.commonAncestorContainer;
		if(e !== l && t !== l || i.contains(n)) return p(l) ? l : r(l);
		var f = s(e);
		return f.host ? d(f.host, t) : d(e, s(t).host)
	}

	function a(e) {
		var t = 1 < arguments.length && void 0 !== arguments[1] ? arguments[1] : 'top',
			o = 'top' === t ? 'scrollTop' : 'scrollLeft',
			i = e.nodeName;
		if('BODY' === i || 'HTML' === i) {
			var n = window.document.documentElement,
				r = window.document.scrollingElement || n;
			return r[o]
		}
		return e[o]
	}

	function l(e, t) {
		var o = 2 < arguments.length && void 0 !== arguments[2] && arguments[2],
			i = a(t, 'top'),
			n = a(t, 'left'),
			r = o ? -1 : 1;
		return e.top += i * r, e.bottom += i * r, e.left += n * r, e.right += n * r, e
	}

	function f(e, t) {
		var o = 'x' === t ? 'Left' : 'Top',
			i = 'Left' == o ? 'Right' : 'Bottom';
		return +e['border' + o + 'Width'].split('px')[0] + +e['border' + i + 'Width'].split('px')[0]
	}

	function m(e, t, o, i) {
		return X(t['offset' + e], t['scroll' + e], o['client' + e], o['offset' + e], o['scroll' + e], ne() ? o['offset' + e] + i['margin' + ('Height' === e ? 'Top' : 'Left')] + i['margin' + ('Height' === e ? 'Bottom' : 'Right')] : 0)
	}

	function c() {
		var e = window.document.body,
			t = window.document.documentElement,
			o = ne() && window.getComputedStyle(t);
		return {
			height: m('Height', e, t, o),
			width: m('Width', e, t, o)
		}
	}

	function h(e) {
		return de({}, e, {
			right: e.left + e.width,
			bottom: e.top + e.height
		})
	}

	function g(e) {
		var o = {};
		if(ne()) try {
			o = e.getBoundingClientRect();
			var i = a(e, 'top'),
				n = a(e, 'left');
			o.top += i, o.left += n, o.bottom += i, o.right += n
		} catch(e) {} else o = e.getBoundingClientRect();
		var r = {
				left: o.left,
				top: o.top,
				width: o.right - o.left,
				height: o.bottom - o.top
			},
			p = 'HTML' === e.nodeName ? c() : {},
			s = p.width || e.clientWidth || r.right - r.left,
			d = p.height || e.clientHeight || r.bottom - r.top,
			l = e.offsetWidth - s,
			m = e.offsetHeight - d;
		if(l || m) {
			var g = t(e);
			l -= f(g, 'x'), m -= f(g, 'y'), r.width -= l, r.height -= m
		}
		return h(r)
	}

	function u(e, o) {
		var i = ne(),
			r = 'HTML' === o.nodeName,
			p = g(e),
			s = g(o),
			d = n(e),
			a = t(o),
			f = +a.borderTopWidth.split('px')[0],
			m = +a.borderLeftWidth.split('px')[0],
			c = h({
				top: p.top - s.top - f,
				left: p.left - s.left - m,
				width: p.width,
				height: p.height
			});
		if(c.marginTop = 0, c.marginLeft = 0, !i && r) {
			var u = +a.marginTop.split('px')[0],
				b = +a.marginLeft.split('px')[0];
			c.top -= f - u, c.bottom -= f - u, c.left -= m - b, c.right -= m - b, c.marginTop = u, c.marginLeft = b
		}
		return(i ? o.contains(d) : o === d && 'BODY' !== d.nodeName) && (c = l(c, o)), c
	}

	function b(e) {
		var t = window.document.documentElement,
			o = u(e, t),
			i = X(t.clientWidth, window.innerWidth || 0),
			n = X(t.clientHeight, window.innerHeight || 0),
			r = a(t),
			p = a(t, 'left'),
			s = {
				top: r - o.top + o.marginTop,
				left: p - o.left + o.marginLeft,
				width: i,
				height: n
			};
		return h(s)
	}

	function y(e) {
		var i = e.nodeName;
		return 'BODY' === i || 'HTML' === i ? !1 : 'fixed' === t(e, 'position') || y(o(e))
	}

	function w(e, t, i, r) {
		var p = {
				top: 0,
				left: 0
			},
			s = d(e, t);
		if('viewport' === r) p = b(s);
		else {
			var a;
			'scrollParent' === r ? (a = n(o(e)), 'BODY' === a.nodeName && (a = window.document.documentElement)) : 'window' === r ? a = window.document.documentElement : a = r;
			var l = u(a, s);
			if('HTML' === a.nodeName && !y(s)) {
				var f = c(),
					m = f.height,
					h = f.width;
				p.top += l.top - l.marginTop, p.bottom = m + l.top, p.left += l.left - l.marginLeft, p.right = h + l.left
			} else p = l
		}
		return p.left += i, p.top += i, p.right -= i, p.bottom -= i, p
	}

	function E(e) {
		var t = e.width,
			o = e.height;
		return t * o
	}

	function v(e, t, o, i, n) {
		var r = 5 < arguments.length && void 0 !== arguments[5] ? arguments[5] : 0;
		if(-1 === e.indexOf('auto')) return e;
		var p = w(o, i, r, n),
			s = {
				top: {
					width: p.width,
					height: t.top - p.top
				},
				right: {
					width: p.right - t.right,
					height: p.height
				},
				bottom: {
					width: p.width,
					height: p.bottom - t.bottom
				},
				left: {
					width: t.left - p.left,
					height: p.height
				}
			},
			d = Object.keys(s).map(function(e) {
				return de({
					key: e
				}, s[e], {
					area: E(s[e])
				})
			}).sort(function(e, t) {
				return t.area - e.area
			}),
			a = d.filter(function(e) {
				var t = e.width,
					i = e.height;
				return t >= o.clientWidth && i >= o.clientHeight
			}),
			l = 0 < a.length ? a[0].key : d[0].key,
			f = e.split('-')[1];
		return l + (f ? '-' + f : '')
	}

	function x(e, t, o) {
		var i = d(t, o);
		return u(o, i)
	}

	function O(e) {
		var t = window.getComputedStyle(e),
			o = parseFloat(t.marginTop) + parseFloat(t.marginBottom),
			i = parseFloat(t.marginLeft) + parseFloat(t.marginRight),
			n = {
				width: e.offsetWidth + i,
				height: e.offsetHeight + o
			};
		return n
	}

	function L(e) {
		var t = {
			left: 'right',
			right: 'left',
			bottom: 'top',
			top: 'bottom'
		};
		return e.replace(/left|right|bottom|top/g, function(e) {
			return t[e]
		})
	}

	function S(e, t, o) {
		o = o.split('-')[0];
		var i = O(e),
			n = {
				width: i.width,
				height: i.height
			},
			r = -1 !== ['right', 'left'].indexOf(o),
			p = r ? 'top' : 'left',
			s = r ? 'left' : 'top',
			d = r ? 'height' : 'width',
			a = r ? 'width' : 'height';
		return n[p] = t[p] + t[d] / 2 - i[d] / 2, n[s] = o === s ? t[s] - i[a] : t[L(s)], n
	}

	function T(e, t) {
		return Array.prototype.find ? e.find(t) : e.filter(t)[0]
	}

	function C(e, t, o) {
		if(Array.prototype.findIndex) return e.findIndex(function(e) {
			return e[t] === o
		});
		var i = T(e, function(e) {
			return e[t] === o
		});
		return e.indexOf(i)
	}

	function N(t, o, i) {
		var n = void 0 === i ? t : t.slice(0, C(t, 'name', i));
		return n.forEach(function(t) {
			t.function && console.warn('`modifier.function` is deprecated, use `modifier.fn`!');
			var i = t.function || t.fn;
			t.enabled && e(i) && (o.offsets.popper = h(o.offsets.popper), o.offsets.reference = h(o.offsets.reference), o = i(o, t))
		}), o
	}

	function k() {
		if(!this.state.isDestroyed) {
			var e = {
				instance: this,
				styles: {},
				arrowStyles: {},
				attributes: {},
				flipped: !1,
				offsets: {}
			};
			e.offsets.reference = x(this.state, this.popper, this.reference), e.placement = v(this.options.placement, e.offsets.reference, this.popper, this.reference, this.options.modifiers.flip.boundariesElement, this.options.modifiers.flip.padding), e.originalPlacement = e.placement, e.offsets.popper = S(this.popper, e.offsets.reference, e.placement), e.offsets.popper.position = 'absolute', e = N(this.modifiers, e), this.state.isCreated ? this.options.onUpdate(e) : (this.state.isCreated = !0, this.options.onCreate(e))
		}
	}

	function W(e, t) {
		return e.some(function(e) {
			var o = e.name,
				i = e.enabled;
			return i && o === t
		})
	}

	function B(e) {
		for(var t = [!1, 'ms', 'Webkit', 'Moz', 'O'], o = e.charAt(0).toUpperCase() + e.slice(1), n = 0; n < t.length - 1; n++) {
			var i = t[n],
				r = i ? '' + i + o : e;
			if('undefined' != typeof window.document.body.style[r]) return r
		}
		return null
	}

	function P() {
		return this.state.isDestroyed = !0, W(this.modifiers, 'applyStyle') && (this.popper.removeAttribute('x-placement'), this.popper.style.left = '', this.popper.style.position = '', this.popper.style.top = '', this.popper.style[B('transform')] = ''), this.disableEventListeners(), this.options.removeOnDestroy && this.popper.parentNode.removeChild(this.popper), this
	}

	function D(e, t, o, i) {
		var r = 'BODY' === e.nodeName,
			p = r ? window : e;
		p.addEventListener(t, o, {
			passive: !0
		}), r || D(n(p.parentNode), t, o, i), i.push(p)
	}

	function H(e, t, o, i) {
		o.updateBound = i, window.addEventListener('resize', o.updateBound, {
			passive: !0
		});
		var r = n(e);
		return D(r, 'scroll', o.updateBound, o.scrollParents), o.scrollElement = r, o.eventsEnabled = !0, o
	}

	function A() {
		this.state.eventsEnabled || (this.state = H(this.reference, this.options, this.state, this.scheduleUpdate))
	}

	function M(e, t) {
		return window.removeEventListener('resize', t.updateBound), t.scrollParents.forEach(function(e) {
			e.removeEventListener('scroll', t.updateBound)
		}), t.updateBound = null, t.scrollParents = [], t.scrollElement = null, t.eventsEnabled = !1, t
	}

	function I() {
		this.state.eventsEnabled && (window.cancelAnimationFrame(this.scheduleUpdate), this.state = M(this.reference, this.state))
	}

	function R(e) {
		return '' !== e && !isNaN(parseFloat(e)) && isFinite(e)
	}

	function U(e, t) {
		Object.keys(t).forEach(function(o) {
			var i = ''; - 1 !== ['width', 'height', 'top', 'right', 'bottom', 'left'].indexOf(o) && R(t[o]) && (i = 'px'), e.style[o] = t[o] + i
		})
	}

	function Y(e, t) {
		Object.keys(t).forEach(function(o) {
			var i = t[o];
			!1 === i ? e.removeAttribute(o) : e.setAttribute(o, t[o])
		})
	}

	function F(e, t, o) {
		var i = T(e, function(e) {
				var o = e.name;
				return o === t
			}),
			n = !!i && e.some(function(e) {
				return e.name === o && e.enabled && e.order < i.order
			});
		if(!n) {
			var r = '`' + t + '`';
			console.warn('`' + o + '`' + ' modifier is required by ' + r + ' modifier in order to work, be sure to include it before ' + r + '!')
		}
		return n
	}

	function j(e) {
		return 'end' === e ? 'start' : 'start' === e ? 'end' : e
	}

	function K(e) {
		var t = 1 < arguments.length && void 0 !== arguments[1] && arguments[1],
			o = le.indexOf(e),
			i = le.slice(o + 1).concat(le.slice(0, o));
		return t ? i.reverse() : i
	}

	function q(e, t, o, i) {
		var n = e.match(/((?:\-|\+)?\d*\.?\d*)(.*)/),
			r = +n[1],
			p = n[2];
		if(!r) return e;
		if(0 === p.indexOf('%')) {
			var s;
			switch(p) {
				case '%p':
					s = o;
					break;
				case '%':
				case '%r':
				default:
					s = i;
			}
			var d = h(s);
			return d[t] / 100 * r
		}
		if('vh' === p || 'vw' === p) {
			var a;
			return a = 'vh' === p ? X(document.documentElement.clientHeight, window.innerHeight || 0) : X(document.documentElement.clientWidth, window.innerWidth || 0), a / 100 * r
		}
		return r
	}

	function G(e, t, o, i) {
		var n = [0, 0],
			r = -1 !== ['right', 'left'].indexOf(i),
			p = e.split(/(\+|\-)/).map(function(e) {
				return e.trim()
			}),
			s = p.indexOf(T(p, function(e) {
				return -1 !== e.search(/,|\s/)
			}));
		p[s] && -1 === p[s].indexOf(',') && console.warn('Offsets separated by white space(s) are deprecated, use a comma (,) instead.');
		var d = /\s*,\s*|\s+/,
			a = -1 === s ? [p] : [p.slice(0, s).concat([p[s].split(d)[0]]), [p[s].split(d)[1]].concat(p.slice(s + 1))];
		return a = a.map(function(e, i) {
			var n = (1 === i ? !r : r) ? 'height' : 'width',
				p = !1;
			return e.reduce(function(e, t) {
				return '' === e[e.length - 1] && -1 !== ['+', '-'].indexOf(t) ? (e[e.length - 1] = t, p = !0, e) : p ? (e[e.length - 1] += t, p = !1, e) : e.concat(t)
			}, []).map(function(e) {
				return q(e, n, t, o)
			})
		}), a.forEach(function(e, t) {
			e.forEach(function(o, i) {
				R(o) && (n[t] += o * ('-' === e[i - 1] ? -1 : 1))
			})
		}), n
	}

	function z(e, t) {
		var o, i = t.offset,
			n = e.placement,
			r = e.offsets,
			p = r.popper,
			s = r.reference,
			d = n.split('-')[0];
		return o = R(+i) ? [+i, 0] : G(i, p, s, d), 'left' === d ? (p.top += o[0], p.left -= o[1]) : 'right' === d ? (p.top += o[0], p.left += o[1]) : 'top' === d ? (p.left += o[0], p.top -= o[1]) : 'bottom' === d && (p.left += o[0], p.top += o[1]), e.popper = p, e
	}
	for(var V = Math.min, _ = Math.floor, X = Math.max, Q = ['native code', '[object MutationObserverConstructor]'], J = function(e) {
			return Q.some(function(t) {
				return -1 < (e || '').toString().indexOf(t)
			})
		}, Z = 'undefined' != typeof window, $ = ['Edge', 'Trident', 'Firefox'], ee = 0, te = 0; te < $.length; te += 1)
		if(Z && 0 <= navigator.userAgent.indexOf($[te])) {
			ee = 1;
			break
		}
	var i, oe = Z && J(window.MutationObserver),
		ie = oe ? function(e) {
			var t = !1,
				o = 0,
				i = document.createElement('span'),
				n = new MutationObserver(function() {
					e(), t = !1
				});
			return n.observe(i, {
					attributes: !0
				}),
				function() {
					t || (t = !0, i.setAttribute('x-index', o), ++o)
				}
		} : function(e) {
			var t = !1;
			return function() {
				t || (t = !0, setTimeout(function() {
					t = !1, e()
				}, ee))
			}
		},
		ne = function() {
			return void 0 == i && (i = -1 !== navigator.appVersion.indexOf('MSIE 10')), i
		},
		re = function(e, t) {
			if(!(e instanceof t)) throw new TypeError('Cannot call a class as a function')
		},
		pe = function() {
			function e(e, t) {
				for(var o, n = 0; n < t.length; n++) o = t[n], o.enumerable = o.enumerable || !1, o.configurable = !0, 'value' in o && (o.writable = !0), Object.defineProperty(e, o.key, o)
			}
			return function(t, o, i) {
				return o && e(t.prototype, o), i && e(t, i), t
			}
		}(),
		se = function(e, t, o) {
			return t in e ? Object.defineProperty(e, t, {
				value: o,
				enumerable: !0,
				configurable: !0,
				writable: !0
			}) : e[t] = o, e
		},
		de = Object.assign || function(e) {
			for(var t, o = 1; o < arguments.length; o++)
				for(var i in t = arguments[o], t) Object.prototype.hasOwnProperty.call(t, i) && (e[i] = t[i]);
			return e
		},
		ae = ['auto-start', 'auto', 'auto-end', 'top-start', 'top', 'top-end', 'right-start', 'right', 'right-end', 'bottom-end', 'bottom', 'bottom-start', 'left-end', 'left', 'left-start'],
		le = ae.slice(3),
		fe = {
			FLIP: 'flip',
			CLOCKWDSE: 'clockwise',
			COUNTERCLOCKWDSE: 'counterclockwise'
		},
		me = function() {
			function t(o, i) {
				var n = this,
					r = 2 < arguments.length && void 0 !== arguments[2] ? arguments[2] : {};
				re(this, t), this.scheduleUpdate = function() {
					return requestAnimationFrame(n.update)
				}, this.update = ie(this.update.bind(this)), this.options = de({}, t.Defaults, r), this.state = {
					isDestroyed: !1,
					isCreated: !1,
					scrollParents: []
				}, this.reference = o.jquery ? o[0] : o, this.popper = i.jquery ? i[0] : i, this.options.modifiers = {}, Object.keys(de({}, t.Defaults.modifiers, r.modifiers)).forEach(function(e) {
					n.options.modifiers[e] = de({}, t.Defaults.modifiers[e] || {}, r.modifiers ? r.modifiers[e] : {})
				}), this.modifiers = Object.keys(this.options.modifiers).map(function(e) {
					return de({
						name: e
					}, n.options.modifiers[e])
				}).sort(function(e, t) {
					return e.order - t.order
				}), this.modifiers.forEach(function(t) {
					t.enabled && e(t.onLoad) && t.onLoad(n.reference, n.popper, n.options, t, n.state)
				}), this.update();
				var p = this.options.eventsEnabled;
				p && this.enableEventListeners(), this.state.eventsEnabled = p
			}
			return pe(t, [{
				key: 'update',
				value: function() {
					return k.call(this)
				}
			}, {
				key: 'destroy',
				value: function() {
					return P.call(this)
				}
			}, {
				key: 'enableEventListeners',
				value: function() {
					return A.call(this)
				}
			}, {
				key: 'disableEventListeners',
				value: function() {
					return I.call(this)
				}
			}]), t
		}();
	return me.Utils = ('undefined' == typeof window ? global : window).PopperUtils, me.placements = ae, me.Defaults = {
		placement: 'bottom',
		eventsEnabled: !0,
		removeOnDestroy: !1,
		onCreate: function() {},
		onUpdate: function() {},
		modifiers: {
			shift: {
				order: 100,
				enabled: !0,
				fn: function(e) {
					var t = e.placement,
						o = t.split('-')[0],
						i = t.split('-')[1];
					if(i) {
						var n = e.offsets,
							r = n.reference,
							p = n.popper,
							s = -1 !== ['bottom', 'top'].indexOf(o),
							d = s ? 'left' : 'top',
							a = s ? 'width' : 'height',
							l = {
								start: se({}, d, r[d]),
								end: se({}, d, r[d] + r[a] - p[a])
							};
						e.offsets.popper = de({}, p, l[i])
					}
					return e
				}
			},
			offset: {
				order: 200,
				enabled: !0,
				fn: z,
				offset: 0
			},
			preventOverflow: {
				order: 300,
				enabled: !0,
				fn: function(e, t) {
					var o = t.boundariesElement || r(e.instance.popper);
					e.instance.reference === o && (o = r(o));
					var i = w(e.instance.popper, e.instance.reference, t.padding, o);
					t.boundaries = i;
					var n = t.priority,
						p = e.offsets.popper,
						s = {
							primary: function(e) {
								var o = p[e];
								return p[e] < i[e] && !t.escapeWithReference && (o = X(p[e], i[e])), se({}, e, o)
							},
							secondary: function(e) {
								var o = 'right' === e ? 'left' : 'top',
									n = p[o];
								return p[e] > i[e] && !t.escapeWithReference && (n = V(p[o], i[e] - ('right' === e ? p.width : p.height))), se({}, o, n)
							}
						};
					return n.forEach(function(e) {
						var t = -1 === ['left', 'top'].indexOf(e) ? 'secondary' : 'primary';
						p = de({}, p, s[t](e))
					}), e.offsets.popper = p, e
				},
				priority: ['left', 'right', 'top', 'bottom'],
				padding: 5,
				boundariesElement: 'scrollParent'
			},
			keepTogether: {
				order: 400,
				enabled: !0,
				fn: function(e) {
					var t = e.offsets,
						o = t.popper,
						i = t.reference,
						n = e.placement.split('-')[0],
						r = _,
						p = -1 !== ['top', 'bottom'].indexOf(n),
						s = p ? 'right' : 'bottom',
						d = p ? 'left' : 'top',
						a = p ? 'width' : 'height';
					return o[s] < r(i[d]) && (e.offsets.popper[d] = r(i[d]) - o[a]), o[d] > r(i[s]) && (e.offsets.popper[d] = r(i[s])), e
				}
			},
			arrow: {
				order: 500,
				enabled: !0,
				fn: function(e, o) {
					if(!F(e.instance.modifiers, 'arrow', 'keepTogether')) return e;
					var i = o.element;
					if('string' == typeof i) {
						if(i = e.instance.popper.querySelector(i), !i) return e;
					} else if(!e.instance.popper.contains(i)) return console.warn('WARNING: `arrow.element` must be child of its popper element!'), e;
					var n = e.placement.split('-')[0],
						r = e.offsets,
						p = r.popper,
						s = r.reference,
						d = -1 !== ['left', 'right'].indexOf(n),
						a = d ? 'height' : 'width',
						l = d ? 'Top' : 'Left',
						f = l.toLowerCase(),
						m = d ? 'left' : 'top',
						c = d ? 'bottom' : 'right',
						g = O(i)[a];
					s[c] - g < p[f] && (e.offsets.popper[f] -= p[f] - (s[c] - g)), s[f] + g > p[c] && (e.offsets.popper[f] += s[f] + g - p[c]);
					var u = s[f] + s[a] / 2 - g / 2,
						b = t(e.instance.popper, 'margin' + l).replace('px', ''),
						y = u - h(e.offsets.popper)[f] - b;
					return y = X(V(p[a] - g, y), 0), e.arrowElement = i, e.offsets.arrow = {}, e.offsets.arrow[f] = Math.round(y), e.offsets.arrow[m] = '', e
				},
				element: '[x-arrow]'
			},
			flip: {
				order: 600,
				enabled: !0,
				fn: function(e, t) {
					if(W(e.instance.modifiers, 'inner')) return e;
					if(e.flipped && e.placement === e.originalPlacement) return e;
					var o = w(e.instance.popper, e.instance.reference, t.padding, t.boundariesElement),
						i = e.placement.split('-')[0],
						n = L(i),
						r = e.placement.split('-')[1] || '',
						p = [];
					switch(t.behavior) {
						case fe.FLIP:
							p = [i, n];
							break;
						case fe.CLOCKWDSE:
							p = K(i);
							break;
						case fe.COUNTERCLOCKWDSE:
							p = K(i, !0);
							break;
						default:
							p = t.behavior;
					}
					return p.forEach(function(s, d) {
						if(i !== s || p.length === d + 1) return e;
						i = e.placement.split('-')[0], n = L(i);
						var a = e.offsets.popper,
							l = e.offsets.reference,
							f = _,
							m = 'left' === i && f(a.right) > f(l.left) || 'right' === i && f(a.left) < f(l.right) || 'top' === i && f(a.bottom) > f(l.top) || 'bottom' === i && f(a.top) < f(l.bottom),
							c = f(a.left) < f(o.left),
							h = f(a.right) > f(o.right),
							g = f(a.top) < f(o.top),
							u = f(a.bottom) > f(o.bottom),
							b = 'left' === i && c || 'right' === i && h || 'top' === i && g || 'bottom' === i && u,
							y = -1 !== ['top', 'bottom'].indexOf(i),
							w = !!t.flipVariations && (y && 'start' === r && c || y && 'end' === r && h || !y && 'start' === r && g || !y && 'end' === r && u);
						(m || b || w) && (e.flipped = !0, (m || b) && (i = p[d + 1]), w && (r = j(r)), e.placement = i + (r ? '-' + r : ''), e.offsets.popper = de({}, e.offsets.popper, S(e.instance.popper, e.offsets.reference, e.placement)), e = N(e.instance.modifiers, e, 'flip'))
					}), e
				},
				behavior: 'flip',
				padding: 5,
				boundariesElement: 'viewport'
			},
			inner: {
				order: 700,
				enabled: !1,
				fn: function(e) {
					var t = e.placement,
						o = t.split('-')[0],
						i = e.offsets,
						n = i.popper,
						r = i.reference,
						p = -1 !== ['left', 'right'].indexOf(o),
						s = -1 === ['top', 'left'].indexOf(o);
					return n[p ? 'left' : 'top'] = r[o] - (s ? n[p ? 'width' : 'height'] : 0), e.placement = L(t), e.offsets.popper = h(n), e
				}
			},
			hide: {
				order: 800,
				enabled: !0,
				fn: function(e) {
					if(!F(e.instance.modifiers, 'hide', 'preventOverflow')) return e;
					var t = e.offsets.reference,
						o = T(e.instance.modifiers, function(e) {
							return 'preventOverflow' === e.name
						}).boundaries;
					if(t.bottom < o.top || t.left > o.right || t.top > o.bottom || t.right < o.left) {
						if(!0 === e.hide) return e;
						e.hide = !0, e.attributes['x-out-of-boundaries'] = ''
					} else {
						if(!1 === e.hide) return e;
						e.hide = !1, e.attributes['x-out-of-boundaries'] = !1
					}
					return e
				}
			},
			computeStyle: {
				order: 850,
				enabled: !0,
				fn: function(e, t) {
					var o = t.x,
						i = t.y,
						n = e.offsets.popper,
						p = T(e.instance.modifiers, function(e) {
							return 'applyStyle' === e.name
						}).gpuAcceleration;
					void 0 !== p && console.warn('WARNING: `gpuAcceleration` option moved to `computeStyle` modifier and will not be supported in future versions of Popper.js!');
					var s, d, a = void 0 === p ? t.gpuAcceleration : p,
						l = r(e.instance.popper),
						f = g(l),
						m = {
							position: n.position
						},
						c = {
							left: _(n.left),
							top: _(n.top),
							bottom: _(n.bottom),
							right: _(n.right)
						},
						h = 'bottom' === o ? 'top' : 'bottom',
						u = 'right' === i ? 'left' : 'right',
						b = B('transform');
					if(d = 'bottom' == h ? -f.height + c.bottom : c.top, s = 'right' == u ? -f.width + c.right : c.left, a && b) m[b] = 'translate3d(' + s + 'px, ' + d + 'px, 0)', m[h] = 0, m[u] = 0, m.willChange = 'transform';
					else {
						var y = 'bottom' == h ? -1 : 1,
							w = 'right' == u ? -1 : 1;
						m[h] = d * y, m[u] = s * w, m.willChange = h + ', ' + u
					}
					var E = {
						"x-placement": e.placement
					};
					return e.attributes = de({}, E, e.attributes), e.styles = de({}, m, e.styles), e.arrowStyles = de({}, e.offsets.arrow, e.arrowStyles), e
				},
				gpuAcceleration: !0,
				x: 'bottom',
				y: 'right'
			},
			applyStyle: {
				order: 900,
				enabled: !0,
				fn: function(e) {
					return U(e.instance.popper, e.styles), Y(e.instance.popper, e.attributes), e.arrowElement && Object.keys(e.arrowStyles).length && U(e.arrowElement, e.arrowStyles), e
				},
				onLoad: function(e, t, o, i, n) {
					var r = x(n, t, e),
						p = v(o.placement, r, t, e, o.modifiers.flip.boundariesElement, o.modifiers.flip.padding);
					return t.setAttribute('x-placement', p), U(t, {
						position: 'absolute'
					}), o
				},
				gpuAcceleration: void 0
			}
		}
	}, me
});
//# sourceMappingURL=popper.min.js.map
// Menu Js File Add From Here
! function(t) {
	"function" == typeof define && define.amd ? define(["jquery"], t) : "object" == typeof module && "object" == typeof module.exports ? module.exports = t(require("jquery")) : t(jQuery)
}(function($) {
	var menuTrees = [],
		mouse = !1,
		touchEvents = "ontouchstart" in window,
		mouseDetectionEnabled = !1,
		requestAnimationFrame = window.requestAnimationFrame || function(t) {
			return setTimeout(t, 1e3 / 60)
		},
		cancelAnimationFrame = window.cancelAnimationFrame || function(t) {
			clearTimeout(t)
		},
		canAnimate = !!$.fn.animate;

	function initMouseDetection(t) {
		var e = ".smartmenus_mouse";
		if(mouseDetectionEnabled || t) mouseDetectionEnabled && t && ($(document).off(e), mouseDetectionEnabled = !1);
		else {
			var i = !0,
				s = null,
				o = {
					mousemove: function(t) {
						var e = {
							x: t.pageX,
							y: t.pageY,
							timeStamp: (new Date).getTime()
						};
						if(s) {
							var o = Math.abs(s.x - e.x),
								a = Math.abs(s.y - e.y);
							if((o > 0 || a > 0) && o <= 2 && a <= 2 && e.timeStamp - s.timeStamp <= 300 && (mouse = !0, i)) {
								var n = $(t.target).closest("a");
								n.is("a") && $.each(menuTrees, function() {
									if($.contains(this.$root[0], n[0])) return this.itemEnter({
										currentTarget: n[0]
									}), !1
								}), i = !1
							}
						}
						s = e
					}
				};
			o[touchEvents ? "touchstart" : "pointerover pointermove pointerout MSPointerOver MSPointerMove MSPointerOut"] = function(t) {
				isTouchEvent(t.originalEvent) && (mouse = !1)
			}, $(document).on(getEventsNS(o, e)), mouseDetectionEnabled = !0
		}
	}

	function isTouchEvent(t) {
		return !/^(4|mouse)$/.test(t.pointerType)
	}

	function getEventsNS(t, e) {
		e || (e = "");
		var i = {};
		for(var s in t) i[s.split(" ").join(e + " ") + e] = t[s];
		return i
	}
	return $.SmartMenus = function(t, e) {
		this.$root = $(t), this.opts = e, this.rootId = "", this.accessIdPrefix = "", this.$subArrow = null, this.activatedItems = [], this.visibleSubMenus = [], this.showTimeout = 0, this.hideTimeout = 0, this.scrollTimeout = 0, this.clickActivated = !1, this.focusActivated = !1, this.zIndexInc = 0, this.idInc = 0, this.$firstLink = null, this.$firstSub = null, this.disabled = !1, this.$disableOverlay = null, this.$touchScrollingSub = null, this.cssTransforms3d = "perspective" in t.style || "webkitPerspective" in t.style, this.wasCollapsible = !1, this.init()
	}, $.extend($.SmartMenus, {
		hideAll: function() {
			$.each(menuTrees, function() {
				this.menuHideAll()
			})
		},
		destroy: function() {
			for(; menuTrees.length;) menuTrees[0].destroy();
			initMouseDetection(!0)
		},
		prototype: {
			init: function(t) {
				var e = this;
				if(!t) {
					menuTrees.push(this), this.rootId = ((new Date).getTime() + Math.random() + "").replace(/\D/g, ""), this.accessIdPrefix = "sm-" + this.rootId + "-", this.$root.hasClass("sm-rtl") && (this.opts.rightToLeftSubMenus = !0);
					var i = ".smartmenus";
					this.$root.data("smartmenus", this).attr("data-smartmenus-id", this.rootId).dataSM("level", 1).on(getEventsNS({
						"mouseover focusin": $.proxy(this.rootOver, this),
						"mouseout focusout": $.proxy(this.rootOut, this),
						keydown: $.proxy(this.rootKeyDown, this)
					}, i)).on(getEventsNS({
						mouseenter: $.proxy(this.itemEnter, this),
						mouseleave: $.proxy(this.itemLeave, this),
						mousedown: $.proxy(this.itemDown, this),
						focus: $.proxy(this.itemFocus, this),
						blur: $.proxy(this.itemBlur, this),
						click: $.proxy(this.itemClick, this)
					}, i), "a"), i += this.rootId, this.opts.hideOnClick && $(document).on(getEventsNS({
						touchstart: $.proxy(this.docTouchStart, this),
						touchmove: $.proxy(this.docTouchMove, this),
						touchend: $.proxy(this.docTouchEnd, this),
						click: $.proxy(this.docClick, this)
					}, i)), $(window).on(getEventsNS({
						"resize orientationchange": $.proxy(this.winResize, this)
					}, i)), this.opts.subIndicators && (this.$subArrow = $("<span/>").addClass("sub-arrow"), this.opts.subIndicatorsText && this.$subArrow.html(this.opts.subIndicatorsText)), initMouseDetection()
				}
				if(this.$firstSub = this.$root.find("ul").each(function() {
						e.menuInit($(this))
					}).eq(0), this.$firstLink = this.$root.find("a").eq(0), this.opts.markCurrentItem) {
					var s = /(index|default)\.[^#\?\/]*/i,
						o = window.location.href.replace(s, ""),
						a = o.replace(/#.*/, "");
					this.$root.find("a:not(.mega-menu a)").each(function() {
						var t = this.href.replace(s, ""),
							i = $(this);
						t != o && t != a || (i.addClass("current"), e.opts.markCurrentTree && i.parentsUntil("[data-smartmenus-id]", "ul").each(function() {
							$(this).dataSM("parent-a").addClass("current")
						}))
					})
				}
				this.wasCollapsible = this.isCollapsible()
			},
			destroy: function(t) {
				if(!t) {
					var e = ".smartmenus";
					this.$root.removeData("smartmenus").removeAttr("data-smartmenus-id").removeDataSM("level").off(e), e += this.rootId, $(document).off(e), $(window).off(e), this.opts.subIndicators && (this.$subArrow = null)
				}
				this.menuHideAll();
				var i = this;
				this.$root.find("ul").each(function() {
					var t = $(this);
					t.dataSM("scroll-arrows") && t.dataSM("scroll-arrows").remove(), t.dataSM("shown-before") && ((i.opts.subMenusMinWidth || i.opts.subMenusMaxWidth) && t.css({
						width: "",
						minWidth: "",
						maxWidth: ""
					}).removeClass("sm-nowrap"), t.dataSM("scroll-arrows") && t.dataSM("scroll-arrows").remove(), t.css({
						zIndex: "",
						top: "",
						left: "",
						marginLeft: "",
						marginTop: "",
						display: ""
					})), 0 == (t.attr("id") || "").indexOf(i.accessIdPrefix) && t.removeAttr("id")
				}).removeDataSM("in-mega").removeDataSM("shown-before").removeDataSM("scroll-arrows").removeDataSM("parent-a").removeDataSM("level").removeDataSM("beforefirstshowfired").removeAttr("role").removeAttr("aria-hidden").removeAttr("aria-labelledby").removeAttr("aria-expanded"), this.$root.find("a.has-submenu").each(function() {
					var t = $(this);
					0 == t.attr("id").indexOf(i.accessIdPrefix) && t.removeAttr("id")
				}).removeClass("has-submenu").removeDataSM("sub").removeAttr("aria-haspopup").removeAttr("aria-controls").removeAttr("aria-expanded").closest("li").removeDataSM("sub"), this.opts.subIndicators && this.$root.find("span.sub-arrow").remove(), this.opts.markCurrentItem && this.$root.find("a.current").removeClass("current"), t || (this.$root = null, this.$firstLink = null, this.$firstSub = null, this.$disableOverlay && (this.$disableOverlay.remove(), this.$disableOverlay = null), menuTrees.splice($.inArray(this, menuTrees), 1))
			},
			disable: function(t) {
				if(!this.disabled) {
					if(this.menuHideAll(), !t && !this.opts.isPopup && this.$root.is(":visible")) {
						var e = this.$root.offset();
						this.$disableOverlay = $('<div class="sm-jquery-disable-overlay"/>').css({
							position: "absolute",
							top: e.top,
							left: e.left,
							width: this.$root.outerWidth(),
							height: this.$root.outerHeight(),
							zIndex: this.getStartZIndex(!0),
							opacity: 0
						}).appendTo(document.body)
					}
					this.disabled = !0
				}
			},
			docClick: function(t) {
				this.$touchScrollingSub ? this.$touchScrollingSub = null : (this.visibleSubMenus.length && !$.contains(this.$root[0], t.target) || $(t.target).closest("a").length) && this.menuHideAll()
			},
			docTouchEnd: function(t) {
				if(this.lastTouch) {
					if(this.visibleSubMenus.length && (void 0 === this.lastTouch.x2 || this.lastTouch.x1 == this.lastTouch.x2) && (void 0 === this.lastTouch.y2 || this.lastTouch.y1 == this.lastTouch.y2) && (!this.lastTouch.target || !$.contains(this.$root[0], this.lastTouch.target))) {
						this.hideTimeout && (clearTimeout(this.hideTimeout), this.hideTimeout = 0);
						var e = this;
						this.hideTimeout = setTimeout(function() {
							e.menuHideAll()
						}, 350)
					}
					this.lastTouch = null
				}
			},
			docTouchMove: function(t) {
				if(this.lastTouch) {
					var e = t.originalEvent.touches[0];
					this.lastTouch.x2 = e.pageX, this.lastTouch.y2 = e.pageY
				}
			},
			docTouchStart: function(t) {
				var e = t.originalEvent.touches[0];
				this.lastTouch = {
					x1: e.pageX,
					y1: e.pageY,
					target: e.target
				}
			},
			enable: function() {
				this.disabled && (this.$disableOverlay && (this.$disableOverlay.remove(), this.$disableOverlay = null), this.disabled = !1)
			},
			getClosestMenu: function(t) {
				for(var e = $(t).closest("ul"); e.dataSM("in-mega");) e = e.parent().closest("ul");
				return e[0] || null
			},
			getHeight: function(t) {
				return this.getOffset(t, !0)
			},
			getOffset: function(t, e) {
				var i;
				"none" == t.css("display") && (i = {
					position: t[0].style.position,
					visibility: t[0].style.visibility
				}, t.css({
					position: "absolute",
					visibility: "hidden"
				}).show());
				var s = t[0].getBoundingClientRect && t[0].getBoundingClientRect(),
					o = s && (e ? s.height || s.bottom - s.top : s.width || s.right - s.left);
				return o || 0 === o || (o = e ? t[0].offsetHeight : t[0].offsetWidth), i && t.hide().css(i), o
			},
			getStartZIndex: function(t) {
				var e = parseInt(this[t ? "$root" : "$firstSub"].css("z-index"));
				return !t && isNaN(e) && (e = parseInt(this.$root.css("z-index"))), isNaN(e) ? 1 : e
			},
			getTouchPoint: function(t) {
				return t.touches && t.touches[0] || t.changedTouches && t.changedTouches[0] || t
			},
			getViewport: function(t) {
				var e = t ? "Height" : "Width",
					i = document.documentElement["client" + e],
					s = window["inner" + e];
				return s && (i = Math.min(i, s)), i
			},
			getViewportHeight: function() {
				return this.getViewport(!0)
			},
			getViewportWidth: function() {
				return this.getViewport()
			},
			getWidth: function(t) {
				return this.getOffset(t)
			},
			handleEvents: function() {
				return !this.disabled && this.isCSSOn()
			},
			handleItemEvents: function(t) {
				return this.handleEvents() && !this.isLinkInMegaMenu(t)
			},
			isCollapsible: function() {
				return "static" == this.$firstSub.css("position")
			},
			isCSSOn: function() {
				return "inline" != this.$firstLink.css("display")
			},
			isFixed: function() {
				var t = "fixed" == this.$root.css("position");
				return t || this.$root.parentsUntil("body").each(function() {
					if("fixed" == $(this).css("position")) return t = !0, !1
				}), t
			},
			isLinkInMegaMenu: function(t) {
				return $(this.getClosestMenu(t[0])).hasClass("mega-menu")
			},
			isTouchMode: function() {
				return !mouse || this.opts.noMouseOver || this.isCollapsible()
			},
			itemActivate: function(t, e) {
				var i = t.closest("ul"),
					s = i.dataSM("level");
				if(s > 1 && (!this.activatedItems[s - 2] || this.activatedItems[s - 2][0] != i.dataSM("parent-a")[0])) {
					var o = this;
					$(i.parentsUntil("[data-smartmenus-id]", "ul").get().reverse()).add(i).each(function() {
						o.itemActivate($(this).dataSM("parent-a"))
					})
				}
				if(this.isCollapsible() && !e || this.menuHideSubMenus(this.activatedItems[s - 1] && this.activatedItems[s - 1][0] == t[0] ? s : s - 1), this.activatedItems[s - 1] = t, !1 !== this.$root.triggerHandler("activate.smapi", t[0])) {
					var a = t.dataSM("sub");
					a && (this.isTouchMode() || !this.opts.showOnClick || this.clickActivated) && this.menuShow(a)
				}
			},
			itemBlur: function(t) {
				var e = $(t.currentTarget);
				this.handleItemEvents(e) && this.$root.triggerHandler("blur.smapi", e[0])
			},
			itemClick: function(t) {
				var e = $(t.currentTarget);
				if(this.handleItemEvents(e)) {
					if(this.$touchScrollingSub && this.$touchScrollingSub[0] == e.closest("ul")[0]) return this.$touchScrollingSub = null, t.stopPropagation(), !1;
					if(!1 === this.$root.triggerHandler("click.smapi", e[0])) return !1;
					var i = e.dataSM("sub"),
						s = !!i && 2 == i.dataSM("level");
					if(i) {
						var o = $(t.target).is(".sub-arrow"),
							a = this.isCollapsible(),
							n = /toggle$/.test(this.opts.collapsibleBehavior),
							r = /link$/.test(this.opts.collapsibleBehavior),
							h = /^accordion/.test(this.opts.collapsibleBehavior);
						if(i.is(":visible")) {
							if(a && (n || o)) return this.itemActivate(e, h), this.menuHide(i), n && (this.focusActivated = !1), !1
						} else if((!r || !a || o) && (this.opts.showOnClick && s && (this.clickActivated = !0), this.itemActivate(e, h), i.is(":visible"))) return this.focusActivated = !0, !1
					}
					return !(this.opts.showOnClick && s || e.hasClass("disabled") || !1 === this.$root.triggerHandler("select.smapi", e[0])) && void 0
				}
			},
			itemDown: function(t) {
				var e = $(t.currentTarget);
				this.handleItemEvents(e) && e.dataSM("mousedown", !0)
			},
			itemEnter: function(t) {
				var e = $(t.currentTarget);
				if(this.handleItemEvents(e)) {
					if(!this.isTouchMode()) {
						this.showTimeout && (clearTimeout(this.showTimeout), this.showTimeout = 0);
						var i = this;
						this.showTimeout = setTimeout(function() {
							i.itemActivate(e)
						}, this.opts.showOnClick && 1 == e.closest("ul").dataSM("level") ? 1 : this.opts.showTimeout)
					}
					this.$root.triggerHandler("mouseenter.smapi", e[0])
				}
			},
			itemFocus: function(t) {
				var e = $(t.currentTarget);
				this.handleItemEvents(e) && (!this.focusActivated || this.isTouchMode() && e.dataSM("mousedown") || this.activatedItems.length && this.activatedItems[this.activatedItems.length - 1][0] == e[0] || this.itemActivate(e, !0), this.$root.triggerHandler("focus.smapi", e[0]))
			},
			itemLeave: function(t) {
				var e = $(t.currentTarget);
				this.handleItemEvents(e) && (this.isTouchMode() || (e[0].blur(), this.showTimeout && (clearTimeout(this.showTimeout), this.showTimeout = 0)), e.removeDataSM("mousedown"), this.$root.triggerHandler("mouseleave.smapi", e[0]))
			},
			menuHide: function(t) {
				if(!1 !== this.$root.triggerHandler("beforehide.smapi", t[0]) && (canAnimate && t.stop(!0, !0), "none" != t.css("display"))) {
					var e = function() {
						t.css("z-index", "")
					};
					this.isCollapsible() ? canAnimate && this.opts.collapsibleHideFunction ? this.opts.collapsibleHideFunction.call(this, t, e) : t.hide(this.opts.collapsibleHideDuration, e) : canAnimate && this.opts.hideFunction ? this.opts.hideFunction.call(this, t, e) : t.hide(this.opts.hideDuration, e), t.dataSM("scroll") && (this.menuScrollStop(t), t.css({
						"touch-action": "",
						"-ms-touch-action": "",
						"-webkit-transform": "",
						transform: ""
					}).off(".smartmenus_scroll").removeDataSM("scroll").dataSM("scroll-arrows").hide()), t.dataSM("parent-a").removeClass("highlighted").attr("aria-expanded", "false"), t.attr({
						"aria-expanded": "false",
						"aria-hidden": "true"
					});
					var i = t.dataSM("level");
					this.activatedItems.splice(i - 1, 1), this.visibleSubMenus.splice($.inArray(t, this.visibleSubMenus), 1), this.$root.triggerHandler("hide.smapi", t[0])
				}
			},
			menuHideAll: function() {
				this.showTimeout && (clearTimeout(this.showTimeout), this.showTimeout = 0);
				for(var t = this.opts.isPopup ? 1 : 0, e = this.visibleSubMenus.length - 1; e >= t; e--) this.menuHide(this.visibleSubMenus[e]);
				this.opts.isPopup && (canAnimate && this.$root.stop(!0, !0), this.$root.is(":visible") && (canAnimate && this.opts.hideFunction ? this.opts.hideFunction.call(this, this.$root) : this.$root.hide(this.opts.hideDuration))), this.activatedItems = [], this.visibleSubMenus = [], this.clickActivated = !1, this.focusActivated = !1, this.zIndexInc = 0, this.$root.triggerHandler("hideAll.smapi")
			},
			menuHideSubMenus: function(t) {
				for(var e = this.activatedItems.length - 1; e >= t; e--) {
					var i = this.activatedItems[e].dataSM("sub");
					i && this.menuHide(i)
				}
			},
			menuInit: function(t) {
				if(!t.dataSM("in-mega")) {
					t.hasClass("mega-menu") && t.find("ul").dataSM("in-mega", !0);
					for(var e = 2, i = t[0];
						(i = i.parentNode.parentNode) != this.$root[0];) e++;
					var s = t.prevAll("a").eq(-1);
					s.length || (s = t.prevAll().find("a").eq(-1)), s.addClass("has-submenu").dataSM("sub", t), t.dataSM("parent-a", s).dataSM("level", e).parent().dataSM("sub", t);
					var o = s.attr("id") || this.accessIdPrefix + ++this.idInc,
						a = t.attr("id") || this.accessIdPrefix + ++this.idInc;
					s.attr({
						id: o,
						"aria-haspopup": "true",
						"aria-controls": a,
						"aria-expanded": "false"
					}), t.attr({
						id: a,
						role: "group",
						"aria-hidden": "true",
						"aria-labelledby": o,
						"aria-expanded": "false"
					}), this.opts.subIndicators && s[this.opts.subIndicatorsPos](this.$subArrow.clone())
				}
			},
			menuPosition: function(t) {
				var e, i, s = t.dataSM("parent-a"),
					o = s.closest("li"),
					a = o.parent(),
					n = t.dataSM("level"),
					r = this.getWidth(t),
					h = this.getHeight(t),
					u = s.offset(),
					l = u.left,
					c = u.top,
					d = this.getWidth(s),
					m = this.getHeight(s),
					p = $(window),
					f = p.scrollLeft(),
					v = p.scrollTop(),
					b = this.getViewportWidth(),
					S = this.getViewportHeight(),
					g = a.parent().is("[data-sm-horizontal-sub]") || 2 == n && !a.hasClass("sm-vertical"),
					M = this.opts.rightToLeftSubMenus && !o.is("[data-sm-reverse]") || !this.opts.rightToLeftSubMenus && o.is("[data-sm-reverse]"),
					w = 2 == n ? this.opts.mainMenuSubOffsetX : this.opts.subMenusSubOffsetX,
					T = 2 == n ? this.opts.mainMenuSubOffsetY : this.opts.subMenusSubOffsetY;
				if(g ? (e = M ? d - r - w : w, i = this.opts.bottomToTopSubMenus ? -h - T : m + T) : (e = M ? w - r : d - w, i = this.opts.bottomToTopSubMenus ? m - T - h : T), this.opts.keepInViewport) {
					var y = l + e,
						I = c + i;
					if(M && y < f ? e = g ? f - y + e : d - w : !M && y + r > f + b && (e = g ? f + b - r - y + e : w - r), g || (h < S && I + h > v + S ? i += v + S - h - I : (h >= S || I < v) && (i += v - I)), g && (I + h > v + S + .49 || I < v) || !g && h > S + .49) {
						var x = this;
						t.dataSM("scroll-arrows") || t.dataSM("scroll-arrows", $([$('<span class="scroll-up"><span class="scroll-up-arrow"></span></span>')[0], $('<span class="scroll-down"><span class="scroll-down-arrow"></span></span>')[0]]).on({
							mouseenter: function() {
								t.dataSM("scroll").up = $(this).hasClass("scroll-up"), x.menuScroll(t)
							},
							mouseleave: function(e) {
								x.menuScrollStop(t), x.menuScrollOut(t, e)
							},
							"mousewheel DOMMouseScroll": function(t) {
								t.preventDefault()
							}
						}).insertAfter(t));
						var A = ".smartmenus_scroll";
						if(t.dataSM("scroll", {
								y: this.cssTransforms3d ? 0 : i - m,
								step: 1,
								itemH: m,
								subH: h,
								arrowDownH: this.getHeight(t.dataSM("scroll-arrows").eq(1))
							}).on(getEventsNS({
								mouseover: function(e) {
									x.menuScrollOver(t, e)
								},
								mouseout: function(e) {
									x.menuScrollOut(t, e)
								},
								"mousewheel DOMMouseScroll": function(e) {
									x.menuScrollMousewheel(t, e)
								}
							}, A)).dataSM("scroll-arrows").css({
								top: "auto",
								left: "0",
								marginLeft: e + (parseInt(t.css("border-left-width")) || 0),
								width: r - (parseInt(t.css("border-left-width")) || 0) - (parseInt(t.css("border-right-width")) || 0),
								zIndex: t.css("z-index")
							}).eq(g && this.opts.bottomToTopSubMenus ? 0 : 1).show(), this.isFixed()) {
							var C = {};
							C[touchEvents ? "touchstart touchmove touchend" : "pointerdown pointermove pointerup MSPointerDown MSPointerMove MSPointerUp"] = function(e) {
								x.menuScrollTouch(t, e)
							}, t.css({
								"touch-action": "none",
								"-ms-touch-action": "none"
							}).on(getEventsNS(C, A))
						}
					}
				}
				t.css({
					top: "auto",
					left: "0",
					marginLeft: e,
					marginTop: i - m
				})
			},
			menuScroll: function(t, e, i) {
				var s, o = t.dataSM("scroll"),
					a = t.dataSM("scroll-arrows"),
					n = o.up ? o.upEnd : o.downEnd;
				if(!e && o.momentum) {
					if(o.momentum *= .92, (s = o.momentum) < .5) return void this.menuScrollStop(t)
				} else s = i || (e || !this.opts.scrollAccelerate ? this.opts.scrollStep : Math.floor(o.step));
				var r = t.dataSM("level");
				if(this.activatedItems[r - 1] && this.activatedItems[r - 1].dataSM("sub") && this.activatedItems[r - 1].dataSM("sub").is(":visible") && this.menuHideSubMenus(r - 1), o.y = o.up && n <= o.y || !o.up && n >= o.y ? o.y : Math.abs(n - o.y) > s ? o.y + (o.up ? s : -s) : n, t.css(this.cssTransforms3d ? {
						"-webkit-transform": "translate3d(0, " + o.y + "px, 0)",
						transform: "translate3d(0, " + o.y + "px, 0)"
					} : {
						marginTop: o.y
					}), mouse && (o.up && o.y > o.downEnd || !o.up && o.y < o.upEnd) && a.eq(o.up ? 1 : 0).show(), o.y == n) mouse && a.eq(o.up ? 0 : 1).hide(), this.menuScrollStop(t);
				else if(!e) {
					this.opts.scrollAccelerate && o.step < this.opts.scrollStep && (o.step += .2);
					var h = this;
					this.scrollTimeout = requestAnimationFrame(function() {
						h.menuScroll(t)
					})
				}
			},
			menuScrollMousewheel: function(t, e) {
				if(this.getClosestMenu(e.target) == t[0]) {
					var i = ((e = e.originalEvent).wheelDelta || -e.detail) > 0;
					t.dataSM("scroll-arrows").eq(i ? 0 : 1).is(":visible") && (t.dataSM("scroll").up = i, this.menuScroll(t, !0))
				}
				e.preventDefault()
			},
			menuScrollOut: function(t, e) {
				mouse && (/^scroll-(up|down)/.test((e.relatedTarget || "").className) || (t[0] == e.relatedTarget || $.contains(t[0], e.relatedTarget)) && this.getClosestMenu(e.relatedTarget) == t[0] || t.dataSM("scroll-arrows").css("visibility", "hidden"))
			},
			menuScrollOver: function(t, e) {
				if(mouse && !/^scroll-(up|down)/.test(e.target.className) && this.getClosestMenu(e.target) == t[0]) {
					this.menuScrollRefreshData(t);
					var i = t.dataSM("scroll"),
						s = $(window).scrollTop() - t.dataSM("parent-a").offset().top - i.itemH;
					t.dataSM("scroll-arrows").eq(0).css("margin-top", s).end().eq(1).css("margin-top", s + this.getViewportHeight() - i.arrowDownH).end().css("visibility", "visible")
				}
			},
			menuScrollRefreshData: function(t) {
				var e = t.dataSM("scroll"),
					i = $(window).scrollTop() - t.dataSM("parent-a").offset().top - e.itemH;
				this.cssTransforms3d && (i = -(parseFloat(t.css("margin-top")) - i)), $.extend(e, {
					upEnd: i,
					downEnd: i + this.getViewportHeight() - e.subH
				})
			},
			menuScrollStop: function(t) {
				if(this.scrollTimeout) return cancelAnimationFrame(this.scrollTimeout), this.scrollTimeout = 0, t.dataSM("scroll").step = 1, !0
			},
			menuScrollTouch: function(t, e) {
				if(isTouchEvent(e = e.originalEvent)) {
					var i = this.getTouchPoint(e);
					if(this.getClosestMenu(i.target) == t[0]) {
						var s = t.dataSM("scroll");
						if(/(start|down)$/i.test(e.type)) this.menuScrollStop(t) ? (e.preventDefault(), this.$touchScrollingSub = t) : this.$touchScrollingSub = null, this.menuScrollRefreshData(t), $.extend(s, {
							touchStartY: i.pageY,
							touchStartTime: e.timeStamp
						});
						else if(/move$/i.test(e.type)) {
							var o = void 0 !== s.touchY ? s.touchY : s.touchStartY;
							if(void 0 !== o && o != i.pageY) {
								this.$touchScrollingSub = t;
								var a = o < i.pageY;
								void 0 !== s.up && s.up != a && $.extend(s, {
									touchStartY: i.pageY,
									touchStartTime: e.timeStamp
								}), $.extend(s, {
									up: a,
									touchY: i.pageY
								}), this.menuScroll(t, !0, Math.abs(i.pageY - o))
							}
							e.preventDefault()
						} else void 0 !== s.touchY && ((s.momentum = 15 * Math.pow(Math.abs(i.pageY - s.touchStartY) / (e.timeStamp - s.touchStartTime), 2)) && (this.menuScrollStop(t), this.menuScroll(t), e.preventDefault()), delete s.touchY)
					}
				}
			},
			menuShow: function(t) {
				if((t.dataSM("beforefirstshowfired") || (t.dataSM("beforefirstshowfired", !0), !1 !== this.$root.triggerHandler("beforefirstshow.smapi", t[0]))) && !1 !== this.$root.triggerHandler("beforeshow.smapi", t[0]) && (t.dataSM("shown-before", !0), canAnimate && t.stop(!0, !0), !t.is(":visible"))) {
					var e = t.dataSM("parent-a"),
						i = this.isCollapsible();
					if((this.opts.keepHighlighted || i) && e.addClass("highlighted"), i) t.removeClass("sm-nowrap").css({
						zIndex: "",
						width: "auto",
						minWidth: "",
						maxWidth: "",
						top: "",
						left: "",
						marginLeft: "",
						marginTop: ""
					});
					else {
						if(t.css("z-index", this.zIndexInc = (this.zIndexInc || this.getStartZIndex()) + 1), (this.opts.subMenusMinWidth || this.opts.subMenusMaxWidth) && (t.css({
								width: "auto",
								minWidth: "",
								maxWidth: ""
							}).addClass("sm-nowrap"), this.opts.subMenusMinWidth && t.css("min-width", this.opts.subMenusMinWidth), this.opts.subMenusMaxWidth)) {
							var s = this.getWidth(t);
							t.css("max-width", this.opts.subMenusMaxWidth), s > this.getWidth(t) && t.removeClass("sm-nowrap").css("width", this.opts.subMenusMaxWidth)
						}
						this.menuPosition(t)
					}
					var o = function() {
						t.css("overflow", "")
					};
					i ? canAnimate && this.opts.collapsibleShowFunction ? this.opts.collapsibleShowFunction.call(this, t, o) : t.show(this.opts.collapsibleShowDuration, o) : canAnimate && this.opts.showFunction ? this.opts.showFunction.call(this, t, o) : t.show(this.opts.showDuration, o), e.attr("aria-expanded", "true"), t.attr({
						"aria-expanded": "true",
						"aria-hidden": "false"
					}), this.visibleSubMenus.push(t), this.$root.triggerHandler("show.smapi", t[0])
				}
			},
			popupHide: function(t) {
				this.hideTimeout && (clearTimeout(this.hideTimeout), this.hideTimeout = 0);
				var e = this;
				this.hideTimeout = setTimeout(function() {
					e.menuHideAll()
				}, t ? 1 : this.opts.hideTimeout)
			},
			popupShow: function(t, e) {
				if(this.opts.isPopup) {
					if(this.hideTimeout && (clearTimeout(this.hideTimeout), this.hideTimeout = 0), this.$root.dataSM("shown-before", !0), canAnimate && this.$root.stop(!0, !0), !this.$root.is(":visible")) {
						this.$root.css({
							left: t,
							top: e
						});
						var i = this,
							s = function() {
								i.$root.css("overflow", "")
							};
						canAnimate && this.opts.showFunction ? this.opts.showFunction.call(this, this.$root, s) : this.$root.show(this.opts.showDuration, s), this.visibleSubMenus[0] = this.$root
					}
				} else alert('SmartMenus jQuery Error:\n\nIf you want to show this menu via the "popupShow" method, set the isPopup:true option.')
			},
			refresh: function() {
				this.destroy(!0), this.init(!0)
			},
			rootKeyDown: function(t) {
				if(this.handleEvents()) switch(t.keyCode) {
					case 27:
						var e = this.activatedItems[0];
						if(e) this.menuHideAll(), e[0].focus(), (i = e.dataSM("sub")) && this.menuHide(i);
						break;
					case 32:
						var i, s = $(t.target);
						if(s.is("a") && this.handleItemEvents(s))(i = s.dataSM("sub")) && !i.is(":visible") && (this.itemClick({
							currentTarget: t.target
						}), t.preventDefault())
				}
			},
			rootOut: function(t) {
				if(this.handleEvents() && !this.isTouchMode() && t.target != this.$root[0] && (this.hideTimeout && (clearTimeout(this.hideTimeout), this.hideTimeout = 0), !this.opts.showOnClick || !this.opts.hideOnClick)) {
					var e = this;
					this.hideTimeout = setTimeout(function() {
						e.menuHideAll()
					}, this.opts.hideTimeout)
				}
			},
			rootOver: function(t) {
				this.handleEvents() && !this.isTouchMode() && t.target != this.$root[0] && this.hideTimeout && (clearTimeout(this.hideTimeout), this.hideTimeout = 0)
			},
			winResize: function(t) {
				if(this.handleEvents()) {
					if(!("onorientationchange" in window) || "orientationchange" == t.type) {
						var e = this.isCollapsible();
						this.wasCollapsible && e || (this.activatedItems.length && this.activatedItems[this.activatedItems.length - 1][0].blur(), this.menuHideAll()), this.wasCollapsible = e
					}
				} else if(this.$disableOverlay) {
					var i = this.$root.offset();
					this.$disableOverlay.css({
						top: i.top,
						left: i.left,
						width: this.$root.outerWidth(),
						height: this.$root.outerHeight()
					})
				}
			}
		}
	}), $.fn.dataSM = function(t, e) {
		return e ? this.data(t + "_smartmenus", e) : this.data(t + "_smartmenus")
	}, $.fn.removeDataSM = function(t) {
		return this.removeData(t + "_smartmenus")
	}, $.fn.smartmenus = function(options) {
		if("string" == typeof options) {
			var args = arguments,
				method = options;
			return Array.prototype.shift.call(args), this.each(function() {
				var t = $(this).data("smartmenus");
				t && t[method] && t[method].apply(t, args)
			})
		}
		return this.each(function() {
			var dataOpts = $(this).data("sm-options") || null;
			if(dataOpts) try {
				dataOpts = eval("(" + dataOpts + ")")
			} catch(t) {
				dataOpts = null, alert('ERROR\n\nSmartMenus jQuery init:\nInvalid "data-sm-options" attribute value syntax.')
			}
			new $.SmartMenus(this, $.extend({}, $.fn.smartmenus.defaults, options, dataOpts))
		})
	}, $.fn.smartmenus.defaults = {
		isPopup: !1,
		mainMenuSubOffsetX: 0,
		mainMenuSubOffsetY: 0,
		subMenusSubOffsetX: 0,
		subMenusSubOffsetY: 0,
		subMenusMinWidth: "10em",
		subMenusMaxWidth: "20em",
		subIndicators: !0,
		subIndicatorsPos: "append",
		subIndicatorsText: "",
		scrollStep: 30,
		scrollAccelerate: !0,
		showTimeout: 250,
		hideTimeout: 500,
		showDuration: 0,
		showFunction: null,
		hideDuration: 0,
		hideFunction: function(t, e) {
			t.fadeOut(200, e)
		},
		collapsibleShowDuration: 0,
		collapsibleShowFunction: function(t, e) {
			t.slideDown(200, e)
		},
		collapsibleHideDuration: 0,
		collapsibleHideFunction: function(t, e) {
			t.slideUp(200, e)
		},
		showOnClick: !1,
		hideOnClick: !0,
		noMouseOver: !1,
		keepInViewport: !0,
		keepHighlighted: !0,
		markCurrentItem: !1,
		markCurrentTree: !0,
		rightToLeftSubMenus: !1,
		bottomToTopSubMenus: !1,
		collapsibleBehavior: "default"
	}, $
});
/*! lazysizes - v3.0.0 */
! function(a, b) {
	var c = b(a, a.document);
	a.lazySizes = c, "object" == typeof module && module.exports && (module.exports = c)
}(window, function(a, b) {
	"use strict";
	if(b.getElementsByClassName) {
		var c, d = b.documentElement,
			e = a.Date,
			f = a.HTMLPictureElement,
			g = "addEventListener",
			h = "getAttribute",
			i = a[g],
			j = a.setTimeout,
			k = a.requestAnimationFrame || j,
			l = a.requestIdleCallback,
			m = /^picture$/i,
			n = ["load", "error", "lazyincluded", "_lazyloaded"],
			o = {},
			p = Array.prototype.forEach,
			q = function(a, b) {
				return o[b] || (o[b] = new RegExp("(\\s|^)" + b + "(\\s|$)")), o[b].test(a[h]("class") || "") && o[b]
			},
			r = function(a, b) {
				q(a, b) || a.setAttribute("class", (a[h]("class") || "").trim() + " " + b)
			},
			s = function(a, b) {
				var c;
				(c = q(a, b)) && a.setAttribute("class", (a[h]("class") || "").replace(c, " "))
			},
			t = function(a, b, c) {
				var d = c ? g : "removeEventListener";
				c && t(a, b), n.forEach(function(c) {
					a[d](c, b)
				})
			},
			u = function(a, c, d, e, f) {
				var g = b.createEvent("CustomEvent");
				return g.initCustomEvent(c, !e, !f, d || {}), a.dispatchEvent(g), g
			},
			v = function(b, d) {
				var e;
				!f && (e = a.picturefill || c.pf) ? e({
					reevaluate: !0,
					elements: [b]
				}) : d && d.src && (b.src = d.src)
			},
			w = function(a, b) {
				return(getComputedStyle(a, null) || {})[b]
			},
			x = function(a, b, d) {
				for(d = d || a.offsetWidth; d < c.minSize && b && !a._lazysizesWidth;) d = b.offsetWidth, b = b.parentNode;
				return d
			},
			y = function() {
				var a, c, d = [],
					e = [],
					f = d,
					g = function() {
						var b = f;
						for(f = d.length ? e : d, a = !0, c = !1; b.length;) b.shift()();
						a = !1
					},
					h = function(d, e) {
						a && !e ? d.apply(this, arguments) : (f.push(d), c || (c = !0, (b.hidden ? j : k)(g)))
					};
				return h._lsFlush = g, h
			}(),
			z = function(a, b) {
				return b ? function() {
					y(a)
				} : function() {
					var b = this,
						c = arguments;
					y(function() {
						a.apply(b, c)
					})
				}
			},
			A = function(a) {
				var b, c = 0,
					d = 125,
					f = 666,
					g = f,
					h = function() {
						b = !1, c = e.now(), a()
					},
					i = l ? function() {
						l(h, {
							timeout: g
						}), g !== f && (g = f)
					} : z(function() {
						j(h)
					}, !0);
				return function(a) {
					var f;
					(a = a === !0) && (g = 44), b || (b = !0, f = d - (e.now() - c), 0 > f && (f = 0), a || 9 > f && l ? i() : j(i, f))
				}
			},
			B = function(a) {
				var b, c, d = 99,
					f = function() {
						b = null, a()
					},
					g = function() {
						var a = e.now() - c;
						d > a ? j(g, d - a) : (l || f)(f)
					};
				return function() {
					c = e.now(), b || (b = j(g, d))
				}
			},
			C = function() {
				var f, k, l, n, o, x, C, E, F, G, H, I, J, K, L, M = /^img$/i,
					N = /^iframe$/i,
					O = "onscroll" in a && !/glebot/.test(navigator.userAgent),
					P = 0,
					Q = 0,
					R = 0,
					S = -1,
					T = function(a) {
						R--, a && a.target && t(a.target, T), (!a || 0 > R || !a.target) && (R = 0)
					},
					U = function(a, c) {
						var e, f = a,
							g = "hidden" == w(b.body, "visibility") || "hidden" != w(a, "visibility");
						for(F -= c, I += c, G -= c, H += c; g && (f = f.offsetParent) && f != b.body && f != d;) g = (w(f, "opacity") || 1) > 0, g && "visible" != w(f, "overflow") && (e = f.getBoundingClientRect(), g = H > e.left && G < e.right && I > e.top - 1 && F < e.bottom + 1);
						return g
					},
					V = function() {
						var a, e, g, i, j, m, n, p, q;
						if((o = c.loadMode) && 8 > R && (a = f.length)) {
							e = 0, S++, null == K && ("expand" in c || (c.expand = d.clientHeight > 500 && d.clientWidth > 500 ? 500 : 370), J = c.expand, K = J * c.expFactor), K > Q && 1 > R && S > 2 && o > 2 && !b.hidden ? (Q = K, S = 0) : Q = o > 1 && S > 1 && 6 > R ? J : P;
							for(; a > e; e++)
								if(f[e] && !f[e]._lazyRace)
									if(O)
										if((p = f[e][h]("data-expand")) && (m = 1 * p) || (m = Q), q !== m && (C = innerWidth + m * L, E = innerHeight + m, n = -1 * m, q = m), g = f[e].getBoundingClientRect(), (I = g.bottom) >= n && (F = g.top) <= E && (H = g.right) >= n * L && (G = g.left) <= C && (I || H || G || F) && (l && 3 > R && !p && (3 > o || 4 > S) || U(f[e], m))) {
											if(ba(f[e]), j = !0, R > 9) break
										} else !j && l && !i && 4 > R && 4 > S && o > 2 && (k[0] || c.preloadAfterLoad) && (k[0] || !p && (I || H || G || F || "auto" != f[e][h](c.sizesAttr))) && (i = k[0] || f[e]);
							else ba(f[e]);
							i && !j && ba(i)
						}
					},
					W = A(V),
					X = function(a) {
						r(a.target, c.loadedClass), s(a.target, c.loadingClass), t(a.target, Z)
					},
					Y = z(X),
					Z = function(a) {
						Y({
							target: a.target
						})
					},
					$ = function(a, b) {
						try {
							a.contentWindow.location.replace(b)
						} catch(c) {
							a.src = b
						}
					},
					_ = function(a) {
						var b, d, e = a[h](c.srcsetAttr);
						(b = c.customMedia[a[h]("data-media") || a[h]("media")]) && a.setAttribute("media", b), e && a.setAttribute("srcset", e), b && (d = a.parentNode, d.insertBefore(a.cloneNode(), a), d.removeChild(a))
					},
					aa = z(function(a, b, d, e, f) {
						var g, i, k, l, o, q;
						(o = u(a, "lazybeforeunveil", b)).defaultPrevented || (e && (d ? r(a, c.autosizesClass) : a.setAttribute("sizes", e)), i = a[h](c.srcsetAttr), g = a[h](c.srcAttr), f && (k = a.parentNode, l = k && m.test(k.nodeName || "")), q = b.firesLoad || "src" in a && (i || g || l), o = {
							target: a
						}, q && (t(a, T, !0), clearTimeout(n), n = j(T, 2500), r(a, c.loadingClass), t(a, Z, !0)), l && p.call(k.getElementsByTagName("source"), _), i ? a.setAttribute("srcset", i) : g && !l && (N.test(a.nodeName) ? $(a, g) : a.src = g), (i || l) && v(a, {
							src: g
						})), a._lazyRace && delete a._lazyRace, s(a, c.lazyClass), y(function() {
							(!q || a.complete && a.naturalWidth > 1) && (q ? T(o) : R--, X(o))
						}, !0)
					}),
					ba = function(a) {
						var b, d = M.test(a.nodeName),
							e = d && (a[h](c.sizesAttr) || a[h]("sizes")),
							f = "auto" == e;
						(!f && l || !d || !a.src && !a.srcset || a.complete || q(a, c.errorClass)) && (b = u(a, "lazyunveilread").detail, f && D.updateElem(a, !0, a.offsetWidth), a._lazyRace = !0, R++, aa(a, b, f, e, d))
					},
					ca = function() {
						if(!l) {
							if(e.now() - x < 999) return void j(ca, 999);
							var a = B(function() {
								c.loadMode = 3, W()
							});
							l = !0, c.loadMode = 3, W(), i("scroll", function() {
								3 == c.loadMode && (c.loadMode = 2), a()
							}, !0)
						}
					};
				return {
					_: function() {
						x = e.now(), f = b.getElementsByClassName(c.lazyClass), k = b.getElementsByClassName(c.lazyClass + " " + c.preloadClass), L = c.hFac, i("scroll", W, !0), i("resize", W, !0), a.MutationObserver ? new MutationObserver(W).observe(d, {
							childList: !0,
							subtree: !0,
							attributes: !0
						}) : (d[g]("DOMNodeInserted", W, !0), d[g]("DOMAttrModified", W, !0), setInterval(W, 999)), i("hashchange", W, !0), ["focus", "mouseover", "click", "load", "transitionend", "animationend", "webkitAnimationEnd"].forEach(function(a) {
							b[g](a, W, !0)
						}), /d$|^c/.test(b.readyState) ? ca() : (i("load", ca), b[g]("DOMContentLoaded", W), j(ca, 2e4)), f.length ? (V(), y._lsFlush()) : W()
					},
					checkElems: W,
					unveil: ba
				}
			}(),
			D = function() {
				var a, d = z(function(a, b, c, d) {
						var e, f, g;
						if(a._lazysizesWidth = d, d += "px", a.setAttribute("sizes", d), m.test(b.nodeName || ""))
							for(e = b.getElementsByTagName("source"), f = 0, g = e.length; g > f; f++) e[f].setAttribute("sizes", d);
						c.detail.dataAttr || v(a, c.detail)
					}),
					e = function(a, b, c) {
						var e, f = a.parentNode;
						f && (c = x(a, f, c), e = u(a, "lazybeforesizes", {
							width: c,
							dataAttr: !!b
						}), e.defaultPrevented || (c = e.detail.width, c && c !== a._lazysizesWidth && d(a, f, e, c)))
					},
					f = function() {
						var b, c = a.length;
						if(c)
							for(b = 0; c > b; b++) e(a[b])
					},
					g = B(f);
				return {
					_: function() {
						a = b.getElementsByClassName(c.autosizesClass), i("resize", g)
					},
					checkElems: g,
					updateElem: e
				}
			}(),
			E = function() {
				E.i || (E.i = !0, D._(), C._())
			};
		return function() {
			var b, d = {
				lazyClass: "lazyload",
				loadedClass: "lazyloaded",
				loadingClass: "lazyloading",
				preloadClass: "lazypreload",
				errorClass: "lazyerror",
				autosizesClass: "lazyautosizes",
				srcAttr: "data-src",
				srcsetAttr: "data-srcset",
				sizesAttr: "data-sizes",
				minSize: 40,
				customMedia: {},
				init: !0,
				expFactor: 1.5,
				hFac: .8,
				loadMode: 2
			};
			c = a.lazySizesConfig || a.lazysizesConfig || {};
			for(b in d) b in c || (c[b] = d[b]);
			a.lazySizesConfig = c, j(function() {
				c.init && E()
			})
		}(), {
			cfg: c,
			autoSizer: D,
			loader: C,
			init: E,
			uP: v,
			aC: r,
			rC: s,
			hC: q,
			fire: u,
			gW: x,
			rAF: y
		}
	}
});
// Bootstrap Js Added From Here
! function(t, e) {
	"object" == typeof exports && "undefined" != typeof module ? e(exports, require("jquery"), require("popper.js")) : "function" == typeof define && define.amd ? define(["exports", "jquery", "popper.js"], e) : e(t.bootstrap = {}, t.jQuery, t.Popper)
}(this, function(t, e, n) {
	"use strict";

	function i(t, e) {
		for(var n = 0; n < e.length; n++) {
			var i = e[n];
			i.enumerable = i.enumerable || !1, i.configurable = !0, "value" in i && (i.writable = !0), Object.defineProperty(t, i.key, i)
		}
	}

	function s(t, e, n) {
		return e && i(t.prototype, e), n && i(t, n), t
	}

	function r(t, e, n) {
		return e in t ? Object.defineProperty(t, e, {
			value: n,
			enumerable: !0,
			configurable: !0,
			writable: !0
		}) : t[e] = n, t
	}

	function o(t) {
		for(var e = 1; e < arguments.length; e++) {
			var n = null != arguments[e] ? arguments[e] : {},
				i = Object.keys(n);
			"function" == typeof Object.getOwnPropertySymbols && (i = i.concat(Object.getOwnPropertySymbols(n).filter(function(t) {
				return Object.getOwnPropertyDescriptor(n, t).enumerable
			}))), i.forEach(function(e) {
				r(t, e, n[e])
			})
		}
		return t
	}
	e = e && e.hasOwnProperty("default") ? e.default : e, n = n && n.hasOwnProperty("default") ? n.default : n;
	var a = function(t) {
			var e = "transitionend";

			function n(e) {
				var n = this,
					s = !1;
				return t(this).one(i.TRANSITION_END, function() {
					s = !0
				}), setTimeout(function() {
					s || i.triggerTransitionEnd(n)
				}, e), this
			}
			var i = {
				TRANSITION_END: "bsTransitionEnd",
				getUID: function(t) {
					do {
						t += ~~(1e6 * Math.random())
					} while (document.getElementById(t));
					return t
				},
				getSelectorFromElement: function(e) {
					var n = e.getAttribute("data-target");
					n && "#" !== n || (n = e.getAttribute("href") || "");
					try {
						return t(document).find(n).length > 0 ? n : null
					} catch(t) {
						return null
					}
				},
				getTransitionDurationFromElement: function(e) {
					if(!e) return 0;
					var n = t(e).css("transition-duration");
					return parseFloat(n) ? (n = n.split(",")[0], 1e3 * parseFloat(n)) : 0
				},
				reflow: function(t) {
					return t.offsetHeight
				},
				triggerTransitionEnd: function(n) {
					t(n).trigger(e)
				},
				supportsTransitionEnd: function() {
					return Boolean(e)
				},
				isElement: function(t) {
					return(t[0] || t).nodeType
				},
				typeCheckConfig: function(t, e, n) {
					for(var s in n)
						if(Object.prototype.hasOwnProperty.call(n, s)) {
							var r = n[s],
								o = e[s],
								a = o && i.isElement(o) ? "element" : (l = o, {}.toString.call(l).match(/\s([a-z]+)/i)[1].toLowerCase());
							if(!new RegExp(r).test(a)) throw new Error(t.toUpperCase() + ': Option "' + s + '" provided type "' + a + '" but expected type "' + r + '".')
						}
					var l
				}
			};
			return t.fn.emulateTransitionEnd = n, t.event.special[i.TRANSITION_END] = {
				bindType: e,
				delegateType: e,
				handle: function(e) {
					if(t(e.target).is(this)) return e.handleObj.handler.apply(this, arguments)
				}
			}, i
		}(e),
		l = function(t) {
			var e = t.fn.alert,
				n = {
					CLOSE: "close.bs.alert",
					CLOSED: "closed.bs.alert",
					CLICK_DATA_API: "click.bs.alert.data-api"
				},
				i = "alert",
				r = "fade",
				o = "show",
				l = function() {
					function e(t) {
						this._element = t
					}
					var l = e.prototype;
					return l.close = function(t) {
						var e = this._element;
						t && (e = this._getRootElement(t)), this._triggerCloseEvent(e).isDefaultPrevented() || this._removeElement(e)
					}, l.dispose = function() {
						t.removeData(this._element, "bs.alert"), this._element = null
					}, l._getRootElement = function(e) {
						var n = a.getSelectorFromElement(e),
							s = !1;
						return n && (s = t(n)[0]), s || (s = t(e).closest("." + i)[0]), s
					}, l._triggerCloseEvent = function(e) {
						var i = t.Event(n.CLOSE);
						return t(e).trigger(i), i
					}, l._removeElement = function(e) {
						var n = this;
						if(t(e).removeClass(o), t(e).hasClass(r)) {
							var i = a.getTransitionDurationFromElement(e);
							t(e).one(a.TRANSITION_END, function(t) {
								return n._destroyElement(e, t)
							}).emulateTransitionEnd(i)
						} else this._destroyElement(e)
					}, l._destroyElement = function(e) {
						t(e).detach().trigger(n.CLOSED).remove()
					}, e._jQueryInterface = function(n) {
						return this.each(function() {
							var i = t(this),
								s = i.data("bs.alert");
							s || (s = new e(this), i.data("bs.alert", s)), "close" === n && s[n](this)
						})
					}, e._handleDismiss = function(t) {
						return function(e) {
							e && e.preventDefault(), t.close(this)
						}
					}, s(e, null, [{
						key: "VERSION",
						get: function() {
							return "4.1.1"
						}
					}]), e
				}();
			return t(document).on(n.CLICK_DATA_API, '[data-dismiss="alert"]', l._handleDismiss(new l)), t.fn.alert = l._jQueryInterface, t.fn.alert.Constructor = l, t.fn.alert.noConflict = function() {
				return t.fn.alert = e, l._jQueryInterface
			}, l
		}(e),
		c = function(t) {
			var e = "button",
				n = t.fn[e],
				i = "active",
				r = "btn",
				o = "focus",
				a = '[data-toggle^="button"]',
				l = '[data-toggle="buttons"]',
				c = "input",
				h = ".active",
				u = ".btn",
				f = {
					CLICK_DATA_API: "click.bs.button.data-api",
					FOCUS_BLUR_DATA_API: "focus.bs.button.data-api blur.bs.button.data-api"
				},
				d = function() {
					function e(t) {
						this._element = t
					}
					var n = e.prototype;
					return n.toggle = function() {
						var e = !0,
							n = !0,
							s = t(this._element).closest(l)[0];
						if(s) {
							var r = t(this._element).find(c)[0];
							if(r) {
								if("radio" === r.type)
									if(r.checked && t(this._element).hasClass(i)) e = !1;
									else {
										var o = t(s).find(h)[0];
										o && t(o).removeClass(i)
									}
								if(e) {
									if(r.hasAttribute("disabled") || s.hasAttribute("disabled") || r.classList.contains("disabled") || s.classList.contains("disabled")) return;
									r.checked = !t(this._element).hasClass(i), t(r).trigger("change")
								}
								r.focus(), n = !1
							}
						}
						n && this._element.setAttribute("aria-pressed", !t(this._element).hasClass(i)), e && t(this._element).toggleClass(i)
					}, n.dispose = function() {
						t.removeData(this._element, "bs.button"), this._element = null
					}, e._jQueryInterface = function(n) {
						return this.each(function() {
							var i = t(this).data("bs.button");
							i || (i = new e(this), t(this).data("bs.button", i)), "toggle" === n && i[n]()
						})
					}, s(e, null, [{
						key: "VERSION",
						get: function() {
							return "4.1.1"
						}
					}]), e
				}();
			return t(document).on(f.CLICK_DATA_API, a, function(e) {
				e.preventDefault();
				var n = e.target;
				t(n).hasClass(r) || (n = t(n).closest(u)), d._jQueryInterface.call(t(n), "toggle")
			}).on(f.FOCUS_BLUR_DATA_API, a, function(e) {
				var n = t(e.target).closest(u)[0];
				t(n).toggleClass(o, /^focus(in)?$/.test(e.type))
			}), t.fn[e] = d._jQueryInterface, t.fn[e].Constructor = d, t.fn[e].noConflict = function() {
				return t.fn[e] = n, d._jQueryInterface
			}, d
		}(e),
		h = function(t) {
			var e = "carousel",
				n = "bs.carousel",
				i = "." + n,
				r = t.fn[e],
				l = {
					interval: 5e3,
					keyboard: !0,
					slide: !1,
					pause: "hover",
					wrap: !0
				},
				c = {
					interval: "(number|boolean)",
					keyboard: "boolean",
					slide: "(boolean|string)",
					pause: "(string|boolean)",
					wrap: "boolean"
				},
				h = "next",
				u = "prev",
				f = "left",
				d = "right",
				_ = {
					SLIDE: "slide" + i,
					SLID: "slid" + i,
					KEYDOWN: "keydown" + i,
					MOUSEENTER: "mouseenter" + i,
					MOUSELEAVE: "mouseleave" + i,
					TOUCHEND: "touchend" + i,
					LOAD_DATA_API: "load.bs.carousel.data-api",
					CLICK_DATA_API: "click.bs.carousel.data-api"
				},
				g = "carousel",
				m = "active",
				p = "slide",
				v = "carousel-item-right",
				E = "carousel-item-left",
				T = "carousel-item-next",
				b = "carousel-item-prev",
				y = {
					ACTIVE: ".active",
					ACTIVE_ITEM: ".active.carousel-item",
					ITEM: ".carousel-item",
					NEXT_PREV: ".carousel-item-next, .carousel-item-prev",
					INDICATORS: ".carousel-indicators",
					DATA_SLIDE: "[data-slide], [data-slide-to]",
					DATA_RIDE: '[data-ride="carousel"]'
				},
				C = function() {
					function r(e, n) {
						this._items = null, this._interval = null, this._activeElement = null, this._isPaused = !1, this._isSliding = !1, this.touchTimeout = null, this._config = this._getConfig(n), this._element = t(e)[0], this._indicatorsElement = t(this._element).find(y.INDICATORS)[0], this._addEventListeners()
					}
					var C = r.prototype;
					return C.next = function() {
						this._isSliding || this._slide(h)
					}, C.nextWhenVisible = function() {
						!document.hidden && t(this._element).is(":visible") && "hidden" !== t(this._element).css("visibility") && this.next()
					}, C.prev = function() {
						this._isSliding || this._slide(u)
					}, C.pause = function(e) {
						e || (this._isPaused = !0), t(this._element).find(y.NEXT_PREV)[0] && (a.triggerTransitionEnd(this._element), this.cycle(!0)), clearInterval(this._interval), this._interval = null
					}, C.cycle = function(t) {
						t || (this._isPaused = !1), this._interval && (clearInterval(this._interval), this._interval = null), this._config.interval && !this._isPaused && (this._interval = setInterval((document.visibilityState ? this.nextWhenVisible : this.next).bind(this), this._config.interval))
					}, C.to = function(e) {
						var n = this;
						this._activeElement = t(this._element).find(y.ACTIVE_ITEM)[0];
						var i = this._getItemIndex(this._activeElement);
						if(!(e > this._items.length - 1 || e < 0))
							if(this._isSliding) t(this._element).one(_.SLID, function() {
								return n.to(e)
							});
							else {
								if(i === e) return this.pause(), void this.cycle();
								var s = e > i ? h : u;
								this._slide(s, this._items[e])
							}
					}, C.dispose = function() {
						t(this._element).off(i), t.removeData(this._element, n), this._items = null, this._config = null, this._element = null, this._interval = null, this._isPaused = null, this._isSliding = null, this._activeElement = null, this._indicatorsElement = null
					}, C._getConfig = function(t) {
						return t = o({}, l, t), a.typeCheckConfig(e, t, c), t
					}, C._addEventListeners = function() {
						var e = this;
						this._config.keyboard && t(this._element).on(_.KEYDOWN, function(t) {
							return e._keydown(t)
						}), "hover" === this._config.pause && (t(this._element).on(_.MOUSEENTER, function(t) {
							return e.pause(t)
						}).on(_.MOUSELEAVE, function(t) {
							return e.cycle(t)
						}), "ontouchstart" in document.documentElement && t(this._element).on(_.TOUCHEND, function() {
							e.pause(), e.touchTimeout && clearTimeout(e.touchTimeout), e.touchTimeout = setTimeout(function(t) {
								return e.cycle(t)
							}, 500 + e._config.interval)
						}))
					}, C._keydown = function(t) {
						if(!/input|textarea/i.test(t.target.tagName)) switch(t.which) {
							case 37:
								t.preventDefault(), this.prev();
								break;
							case 39:
								t.preventDefault(), this.next()
						}
					}, C._getItemIndex = function(e) {
						return this._items = t.makeArray(t(e).parent().find(y.ITEM)), this._items.indexOf(e)
					}, C._getItemByDirection = function(t, e) {
						var n = t === h,
							i = t === u,
							s = this._getItemIndex(e),
							r = this._items.length - 1;
						if((i && 0 === s || n && s === r) && !this._config.wrap) return e;
						var o = (s + (t === u ? -1 : 1)) % this._items.length;
						return -1 === o ? this._items[this._items.length - 1] : this._items[o]
					}, C._triggerSlideEvent = function(e, n) {
						var i = this._getItemIndex(e),
							s = this._getItemIndex(t(this._element).find(y.ACTIVE_ITEM)[0]),
							r = t.Event(_.SLIDE, {
								relatedTarget: e,
								direction: n,
								from: s,
								to: i
							});
						return t(this._element).trigger(r), r
					}, C._setActiveIndicatorElement = function(e) {
						if(this._indicatorsElement) {
							t(this._indicatorsElement).find(y.ACTIVE).removeClass(m);
							var n = this._indicatorsElement.children[this._getItemIndex(e)];
							n && t(n).addClass(m)
						}
					}, C._slide = function(e, n) {
						var i, s, r, o = this,
							l = t(this._element).find(y.ACTIVE_ITEM)[0],
							c = this._getItemIndex(l),
							u = n || l && this._getItemByDirection(e, l),
							g = this._getItemIndex(u),
							C = Boolean(this._interval);
						if(e === h ? (i = E, s = T, r = f) : (i = v, s = b, r = d), u && t(u).hasClass(m)) this._isSliding = !1;
						else if(!this._triggerSlideEvent(u, r).isDefaultPrevented() && l && u) {
							this._isSliding = !0, C && this.pause(), this._setActiveIndicatorElement(u);
							var I = t.Event(_.SLID, {
								relatedTarget: u,
								direction: r,
								from: c,
								to: g
							});
							if(t(this._element).hasClass(p)) {
								t(u).addClass(s), a.reflow(u), t(l).addClass(i), t(u).addClass(i);
								var A = a.getTransitionDurationFromElement(l);
								t(l).one(a.TRANSITION_END, function() {
									t(u).removeClass(i + " " + s).addClass(m), t(l).removeClass(m + " " + s + " " + i), o._isSliding = !1, setTimeout(function() {
										return t(o._element).trigger(I)
									}, 0)
								}).emulateTransitionEnd(A)
							} else t(l).removeClass(m), t(u).addClass(m), this._isSliding = !1, t(this._element).trigger(I);
							C && this.cycle()
						}
					}, r._jQueryInterface = function(e) {
						return this.each(function() {
							var i = t(this).data(n),
								s = o({}, l, t(this).data());
							"object" == typeof e && (s = o({}, s, e));
							var a = "string" == typeof e ? e : s.slide;
							if(i || (i = new r(this, s), t(this).data(n, i)), "number" == typeof e) i.to(e);
							else if("string" == typeof a) {
								if(void 0 === i[a]) throw new TypeError('No method named "' + a + '"');
								i[a]()
							} else s.interval && (i.pause(), i.cycle())
						})
					}, r._dataApiClickHandler = function(e) {
						var i = a.getSelectorFromElement(this);
						if(i) {
							var s = t(i)[0];
							if(s && t(s).hasClass(g)) {
								var l = o({}, t(s).data(), t(this).data()),
									c = this.getAttribute("data-slide-to");
								c && (l.interval = !1), r._jQueryInterface.call(t(s), l), c && t(s).data(n).to(c), e.preventDefault()
							}
						}
					}, s(r, null, [{
						key: "VERSION",
						get: function() {
							return "4.1.1"
						}
					}, {
						key: "Default",
						get: function() {
							return l
						}
					}]), r
				}();
			return t(document).on(_.CLICK_DATA_API, y.DATA_SLIDE, C._dataApiClickHandler), t(window).on(_.LOAD_DATA_API, function() {
				t(y.DATA_RIDE).each(function() {
					var e = t(this);
					C._jQueryInterface.call(e, e.data())
				})
			}), t.fn[e] = C._jQueryInterface, t.fn[e].Constructor = C, t.fn[e].noConflict = function() {
				return t.fn[e] = r, C._jQueryInterface
			}, C
		}(e),
		u = function(t) {
			var e = "collapse",
				n = "bs.collapse",
				i = t.fn[e],
				r = {
					toggle: !0,
					parent: ""
				},
				l = {
					toggle: "boolean",
					parent: "(string|element)"
				},
				c = {
					SHOW: "show.bs.collapse",
					SHOWN: "shown.bs.collapse",
					HIDE: "hide.bs.collapse",
					HIDDEN: "hidden.bs.collapse",
					CLICK_DATA_API: "click.bs.collapse.data-api"
				},
				h = "show",
				u = "collapse",
				f = "collapsing",
				d = "collapsed",
				_ = "width",
				g = "height",
				m = {
					ACTIVES: ".show, .collapsing",
					DATA_TOGGLE: '[data-toggle="collapse"]'
				},
				p = function() {
					function i(e, n) {
						this._isTransitioning = !1, this._element = e, this._config = this._getConfig(n), this._triggerArray = t.makeArray(t('[data-toggle="collapse"][href="#' + e.id + '"],[data-toggle="collapse"][data-target="#' + e.id + '"]'));
						for(var i = t(m.DATA_TOGGLE), s = 0; s < i.length; s++) {
							var r = i[s],
								o = a.getSelectorFromElement(r);
							null !== o && t(o).filter(e).length > 0 && (this._selector = o, this._triggerArray.push(r))
						}
						this._parent = this._config.parent ? this._getParent() : null, this._config.parent || this._addAriaAndCollapsedClass(this._element, this._triggerArray), this._config.toggle && this.toggle()
					}
					var p = i.prototype;
					return p.toggle = function() {
						t(this._element).hasClass(h) ? this.hide() : this.show()
					}, p.show = function() {
						var e, s, r = this;
						if(!this._isTransitioning && !t(this._element).hasClass(h) && (this._parent && 0 === (e = t.makeArray(t(this._parent).find(m.ACTIVES).filter('[data-parent="' + this._config.parent + '"]'))).length && (e = null), !(e && (s = t(e).not(this._selector).data(n)) && s._isTransitioning))) {
							var o = t.Event(c.SHOW);
							if(t(this._element).trigger(o), !o.isDefaultPrevented()) {
								e && (i._jQueryInterface.call(t(e).not(this._selector), "hide"), s || t(e).data(n, null));
								var l = this._getDimension();
								t(this._element).removeClass(u).addClass(f), this._element.style[l] = 0, this._triggerArray.length > 0 && t(this._triggerArray).removeClass(d).attr("aria-expanded", !0), this.setTransitioning(!0);
								var _ = "scroll" + (l[0].toUpperCase() + l.slice(1)),
									g = a.getTransitionDurationFromElement(this._element);
								t(this._element).one(a.TRANSITION_END, function() {
									t(r._element).removeClass(f).addClass(u).addClass(h), r._element.style[l] = "", r.setTransitioning(!1), t(r._element).trigger(c.SHOWN)
								}).emulateTransitionEnd(g), this._element.style[l] = this._element[_] + "px"
							}
						}
					}, p.hide = function() {
						var e = this;
						if(!this._isTransitioning && t(this._element).hasClass(h)) {
							var n = t.Event(c.HIDE);
							if(t(this._element).trigger(n), !n.isDefaultPrevented()) {
								var i = this._getDimension();
								if(this._element.style[i] = this._element.getBoundingClientRect()[i] + "px", a.reflow(this._element), t(this._element).addClass(f).removeClass(u).removeClass(h), this._triggerArray.length > 0)
									for(var s = 0; s < this._triggerArray.length; s++) {
										var r = this._triggerArray[s],
											o = a.getSelectorFromElement(r);
										if(null !== o) t(o).hasClass(h) || t(r).addClass(d).attr("aria-expanded", !1)
									}
								this.setTransitioning(!0);
								this._element.style[i] = "";
								var l = a.getTransitionDurationFromElement(this._element);
								t(this._element).one(a.TRANSITION_END, function() {
									e.setTransitioning(!1), t(e._element).removeClass(f).addClass(u).trigger(c.HIDDEN)
								}).emulateTransitionEnd(l)
							}
						}
					}, p.setTransitioning = function(t) {
						this._isTransitioning = t
					}, p.dispose = function() {
						t.removeData(this._element, n), this._config = null, this._parent = null, this._element = null, this._triggerArray = null, this._isTransitioning = null
					}, p._getConfig = function(t) {
						return(t = o({}, r, t)).toggle = Boolean(t.toggle), a.typeCheckConfig(e, t, l), t
					}, p._getDimension = function() {
						return t(this._element).hasClass(_) ? _ : g
					}, p._getParent = function() {
						var e = this,
							n = null;
						a.isElement(this._config.parent) ? (n = this._config.parent, void 0 !== this._config.parent.jquery && (n = this._config.parent[0])) : n = t(this._config.parent)[0];
						var s = '[data-toggle="collapse"][data-parent="' + this._config.parent + '"]';
						return t(n).find(s).each(function(t, n) {
							e._addAriaAndCollapsedClass(i._getTargetFromElement(n), [n])
						}), n
					}, p._addAriaAndCollapsedClass = function(e, n) {
						if(e) {
							var i = t(e).hasClass(h);
							n.length > 0 && t(n).toggleClass(d, !i).attr("aria-expanded", i)
						}
					}, i._getTargetFromElement = function(e) {
						var n = a.getSelectorFromElement(e);
						return n ? t(n)[0] : null
					}, i._jQueryInterface = function(e) {
						return this.each(function() {
							var s = t(this),
								a = s.data(n),
								l = o({}, r, s.data(), "object" == typeof e && e ? e : {});
							if(!a && l.toggle && /show|hide/.test(e) && (l.toggle = !1), a || (a = new i(this, l), s.data(n, a)), "string" == typeof e) {
								if(void 0 === a[e]) throw new TypeError('No method named "' + e + '"');
								a[e]()
							}
						})
					}, s(i, null, [{
						key: "VERSION",
						get: function() {
							return "4.1.1"
						}
					}, {
						key: "Default",
						get: function() {
							return r
						}
					}]), i
				}();
			return t(document).on(c.CLICK_DATA_API, m.DATA_TOGGLE, function(e) {
				"A" === e.currentTarget.tagName && e.preventDefault();
				var i = t(this),
					s = a.getSelectorFromElement(this);
				t(s).each(function() {
					var e = t(this),
						s = e.data(n) ? "toggle" : i.data();
					p._jQueryInterface.call(e, s)
				})
			}), t.fn[e] = p._jQueryInterface, t.fn[e].Constructor = p, t.fn[e].noConflict = function() {
				return t.fn[e] = i, p._jQueryInterface
			}, p
		}(e),
		f = function(t) {
			var e = "dropdown",
				i = "bs.dropdown",
				r = "." + i,
				l = t.fn[e],
				c = new RegExp("38|40|27"),
				h = {
					HIDE: "hide" + r,
					HIDDEN: "hidden" + r,
					SHOW: "show" + r,
					SHOWN: "shown" + r,
					CLICK: "click" + r,
					CLICK_DATA_API: "click.bs.dropdown.data-api",
					KEYDOWN_DATA_API: "keydown.bs.dropdown.data-api",
					KEYUP_DATA_API: "keyup.bs.dropdown.data-api"
				},
				u = "disabled",
				f = "show",
				d = "dropup",
				_ = "dropright",
				g = "dropleft",
				m = "dropdown-menu-right",
				p = "position-static",
				v = '[data-toggle="dropdown"]',
				E = ".dropdown form",
				T = ".dropdown-menu",
				b = ".navbar-nav",
				y = ".dropdown-menu .dropdown-item:not(.disabled):not(:disabled)",
				C = "top-start",
				I = "top-end",
				A = "bottom-start",
				D = "bottom-end",
				S = "right-start",
				w = "left-start",
				N = {
					offset: 0,
					flip: !0,
					boundary: "scrollParent",
					reference: "toggle",
					display: "dynamic"
				},
				O = {
					offset: "(number|string|function)",
					flip: "boolean",
					boundary: "(string|element)",
					reference: "(string|element)",
					display: "string"
				},
				k = function() {
					function l(t, e) {
						this._element = t, this._popper = null, this._config = this._getConfig(e), this._menu = this._getMenuElement(), this._inNavbar = this._detectNavbar(), this._addEventListeners()
					}
					var E = l.prototype;
					return E.toggle = function() {
						if(!this._element.disabled && !t(this._element).hasClass(u)) {
							var e = l._getParentFromElement(this._element),
								i = t(this._menu).hasClass(f);
							if(l._clearMenus(), !i) {
								var s = {
										relatedTarget: this._element
									},
									r = t.Event(h.SHOW, s);
								if(t(e).trigger(r), !r.isDefaultPrevented()) {
									if(!this._inNavbar) {
										if(void 0 === n) throw new TypeError("Bootstrap dropdown require Popper.js (https://popper.js.org)");
										var o = this._element;
										"parent" === this._config.reference ? o = e : a.isElement(this._config.reference) && (o = this._config.reference, void 0 !== this._config.reference.jquery && (o = this._config.reference[0])), "scrollParent" !== this._config.boundary && t(e).addClass(p), this._popper = new n(o, this._menu, this._getPopperConfig())
									}
									"ontouchstart" in document.documentElement && 0 === t(e).closest(b).length && t(document.body).children().on("mouseover", null, t.noop), this._element.focus(), this._element.setAttribute("aria-expanded", !0), t(this._menu).toggleClass(f), t(e).toggleClass(f).trigger(t.Event(h.SHOWN, s))
								}
							}
						}
					}, E.dispose = function() {
						t.removeData(this._element, i), t(this._element).off(r), this._element = null, this._menu = null, null !== this._popper && (this._popper.destroy(), this._popper = null)
					}, E.update = function() {
						this._inNavbar = this._detectNavbar(), null !== this._popper && this._popper.scheduleUpdate()
					}, E._addEventListeners = function() {
						var e = this;
						t(this._element).on(h.CLICK, function(t) {
							t.preventDefault(), t.stopPropagation(), e.toggle()
						})
					}, E._getConfig = function(n) {
						return n = o({}, this.constructor.Default, t(this._element).data(), n), a.typeCheckConfig(e, n, this.constructor.DefaultType), n
					}, E._getMenuElement = function() {
						if(!this._menu) {
							var e = l._getParentFromElement(this._element);
							this._menu = t(e).find(T)[0]
						}
						return this._menu
					}, E._getPlacement = function() {
						var e = t(this._element).parent(),
							n = A;
						return e.hasClass(d) ? (n = C, t(this._menu).hasClass(m) && (n = I)) : e.hasClass(_) ? n = S : e.hasClass(g) ? n = w : t(this._menu).hasClass(m) && (n = D), n
					}, E._detectNavbar = function() {
						return t(this._element).closest(".navbar").length > 0
					}, E._getPopperConfig = function() {
						var t = this,
							e = {};
						"function" == typeof this._config.offset ? e.fn = function(e) {
							return e.offsets = o({}, e.offsets, t._config.offset(e.offsets) || {}), e
						} : e.offset = this._config.offset;
						var n = {
							placement: this._getPlacement(),
							modifiers: {
								offset: e,
								flip: {
									enabled: this._config.flip
								},
								preventOverflow: {
									boundariesElement: this._config.boundary
								}
							}
						};
						return "static" === this._config.display && (n.modifiers.applyStyle = {
							enabled: !1
						}), n
					}, l._jQueryInterface = function(e) {
						return this.each(function() {
							var n = t(this).data(i);
							if(n || (n = new l(this, "object" == typeof e ? e : null), t(this).data(i, n)), "string" == typeof e) {
								if(void 0 === n[e]) throw new TypeError('No method named "' + e + '"');
								n[e]()
							}
						})
					}, l._clearMenus = function(e) {
						if(!e || 3 !== e.which && ("keyup" !== e.type || 9 === e.which))
							for(var n = t.makeArray(t(v)), s = 0; s < n.length; s++) {
								var r = l._getParentFromElement(n[s]),
									o = t(n[s]).data(i),
									a = {
										relatedTarget: n[s]
									};
								if(o) {
									var c = o._menu;
									if(t(r).hasClass(f) && !(e && ("click" === e.type && /input|textarea/i.test(e.target.tagName) || "keyup" === e.type && 9 === e.which) && t.contains(r, e.target))) {
										var u = t.Event(h.HIDE, a);
										t(r).trigger(u), u.isDefaultPrevented() || ("ontouchstart" in document.documentElement && t(document.body).children().off("mouseover", null, t.noop), n[s].setAttribute("aria-expanded", "false"), t(c).removeClass(f), t(r).removeClass(f).trigger(t.Event(h.HIDDEN, a)))
									}
								}
							}
					}, l._getParentFromElement = function(e) {
						var n, i = a.getSelectorFromElement(e);
						return i && (n = t(i)[0]), n || e.parentNode
					}, l._dataApiKeydownHandler = function(e) {
						if((/input|textarea/i.test(e.target.tagName) ? !(32 === e.which || 27 !== e.which && (40 !== e.which && 38 !== e.which || t(e.target).closest(T).length)) : c.test(e.which)) && (e.preventDefault(), e.stopPropagation(), !this.disabled && !t(this).hasClass(u))) {
							var n = l._getParentFromElement(this),
								i = t(n).hasClass(f);
							if((i || 27 === e.which && 32 === e.which) && (!i || 27 !== e.which && 32 !== e.which)) {
								var s = t(n).find(y).get();
								if(0 !== s.length) {
									var r = s.indexOf(e.target);
									38 === e.which && r > 0 && r--, 40 === e.which && r < s.length - 1 && r++, r < 0 && (r = 0), s[r].focus()
								}
							} else {
								if(27 === e.which) {
									var o = t(n).find(v)[0];
									t(o).trigger("focus")
								}
								t(this).trigger("click")
							}
						}
					}, s(l, null, [{
						key: "VERSION",
						get: function() {
							return "4.1.1"
						}
					}, {
						key: "Default",
						get: function() {
							return N
						}
					}, {
						key: "DefaultType",
						get: function() {
							return O
						}
					}]), l
				}();
			return t(document).on(h.KEYDOWN_DATA_API, v, k._dataApiKeydownHandler).on(h.KEYDOWN_DATA_API, T, k._dataApiKeydownHandler).on(h.CLICK_DATA_API + " " + h.KEYUP_DATA_API, k._clearMenus).on(h.CLICK_DATA_API, v, function(e) {
				e.preventDefault(), e.stopPropagation(), k._jQueryInterface.call(t(this), "toggle")
			}).on(h.CLICK_DATA_API, E, function(t) {
				t.stopPropagation()
			}), t.fn[e] = k._jQueryInterface, t.fn[e].Constructor = k, t.fn[e].noConflict = function() {
				return t.fn[e] = l, k._jQueryInterface
			}, k
		}(e),
		d = function(t) {
			var e = "modal",
				n = ".bs.modal",
				i = t.fn.modal,
				r = {
					backdrop: !0,
					keyboard: !0,
					focus: !0,
					show: !0
				},
				l = {
					backdrop: "(boolean|string)",
					keyboard: "boolean",
					focus: "boolean",
					show: "boolean"
				},
				c = {
					HIDE: "hide.bs.modal",
					HIDDEN: "hidden.bs.modal",
					SHOW: "show.bs.modal",
					SHOWN: "shown.bs.modal",
					FOCUSIN: "focusin.bs.modal",
					RESIZE: "resize.bs.modal",
					CLICK_DISMISS: "click.dismiss.bs.modal",
					KEYDOWN_DISMISS: "keydown.dismiss.bs.modal",
					MOUSEUP_DISMISS: "mouseup.dismiss.bs.modal",
					MOUSEDOWN_DISMISS: "mousedown.dismiss.bs.modal",
					CLICK_DATA_API: "click.bs.modal.data-api"
				},
				h = "modal-scrollbar-measure",
				u = "modal-backdrop",
				f = "modal-open",
				d = "fade",
				_ = "show",
				g = {
					DIALOG: ".modal-dialog",
					DATA_TOGGLE: '[data-toggle="modal"]',
					DATA_DISMISS: '[data-dismiss="modal"]',
					FIXED_CONTENT: ".fixed-top, .fixed-bottom, .is-fixed, .sticky-top",
					STICKY_CONTENT: ".sticky-top",
					NAVBAR_TOGGLER: ".navbar-toggler"
				},
				m = function() {
					function i(e, n) {
						this._config = this._getConfig(n), this._element = e, this._dialog = t(e).find(g.DIALOG)[0], this._backdrop = null, this._isShown = !1, this._isBodyOverflowing = !1, this._ignoreBackdropClick = !1, this._scrollbarWidth = 0
					}
					var m = i.prototype;
					return m.toggle = function(t) {
						return this._isShown ? this.hide() : this.show(t)
					}, m.show = function(e) {
						var n = this;
						if(!this._isTransitioning && !this._isShown) {
							t(this._element).hasClass(d) && (this._isTransitioning = !0);
							var i = t.Event(c.SHOW, {
								relatedTarget: e
							});
							t(this._element).trigger(i), this._isShown || i.isDefaultPrevented() || (this._isShown = !0, this._checkScrollbar(), this._setScrollbar(), this._adjustDialog(), t(document.body).addClass(f), this._setEscapeEvent(), this._setResizeEvent(), t(this._element).on(c.CLICK_DISMISS, g.DATA_DISMISS, function(t) {
								return n.hide(t)
							}), t(this._dialog).on(c.MOUSEDOWN_DISMISS, function() {
								t(n._element).one(c.MOUSEUP_DISMISS, function(e) {
									t(e.target).is(n._element) && (n._ignoreBackdropClick = !0)
								})
							}), this._showBackdrop(function() {
								return n._showElement(e)
							}))
						}
					}, m.hide = function(e) {
						var n = this;
						if(e && e.preventDefault(), !this._isTransitioning && this._isShown) {
							var i = t.Event(c.HIDE);
							if(t(this._element).trigger(i), this._isShown && !i.isDefaultPrevented()) {
								this._isShown = !1;
								var s = t(this._element).hasClass(d);
								if(s && (this._isTransitioning = !0), this._setEscapeEvent(), this._setResizeEvent(), t(document).off(c.FOCUSIN), t(this._element).removeClass(_), t(this._element).off(c.CLICK_DISMISS), t(this._dialog).off(c.MOUSEDOWN_DISMISS), s) {
									var r = a.getTransitionDurationFromElement(this._element);
									t(this._element).one(a.TRANSITION_END, function(t) {
										return n._hideModal(t)
									}).emulateTransitionEnd(r)
								} else this._hideModal()
							}
						}
					}, m.dispose = function() {
						t.removeData(this._element, "bs.modal"), t(window, document, this._element, this._backdrop).off(n), this._config = null, this._element = null, this._dialog = null, this._backdrop = null, this._isShown = null, this._isBodyOverflowing = null, this._ignoreBackdropClick = null, this._scrollbarWidth = null
					}, m.handleUpdate = function() {
						this._adjustDialog()
					}, m._getConfig = function(t) {
						return t = o({}, r, t), a.typeCheckConfig(e, t, l), t
					}, m._showElement = function(e) {
						var n = this,
							i = t(this._element).hasClass(d);
						this._element.parentNode && this._element.parentNode.nodeType === Node.ELEMENT_NODE || document.body.appendChild(this._element), this._element.style.display = "block", this._element.removeAttribute("aria-hidden"), this._element.scrollTop = 0, i && a.reflow(this._element), t(this._element).addClass(_), this._config.focus && this._enforceFocus();
						var s = t.Event(c.SHOWN, {
								relatedTarget: e
							}),
							r = function() {
								n._config.focus && n._element.focus(), n._isTransitioning = !1, t(n._element).trigger(s)
							};
						if(i) {
							var o = a.getTransitionDurationFromElement(this._element);
							t(this._dialog).one(a.TRANSITION_END, r).emulateTransitionEnd(o)
						} else r()
					}, m._enforceFocus = function() {
						var e = this;
						t(document).off(c.FOCUSIN).on(c.FOCUSIN, function(n) {
							document !== n.target && e._element !== n.target && 0 === t(e._element).has(n.target).length && e._element.focus()
						})
					}, m._setEscapeEvent = function() {
						var e = this;
						this._isShown && this._config.keyboard ? t(this._element).on(c.KEYDOWN_DISMISS, function(t) {
							27 === t.which && (t.preventDefault(), e.hide())
						}) : this._isShown || t(this._element).off(c.KEYDOWN_DISMISS)
					}, m._setResizeEvent = function() {
						var e = this;
						this._isShown ? t(window).on(c.RESIZE, function(t) {
							return e.handleUpdate(t)
						}) : t(window).off(c.RESIZE)
					}, m._hideModal = function() {
						var e = this;
						this._element.style.display = "none", this._element.setAttribute("aria-hidden", !0), this._isTransitioning = !1, this._showBackdrop(function() {
							t(document.body).removeClass(f), e._resetAdjustments(), e._resetScrollbar(), t(e._element).trigger(c.HIDDEN)
						})
					}, m._removeBackdrop = function() {
						this._backdrop && (t(this._backdrop).remove(), this._backdrop = null)
					}, m._showBackdrop = function(e) {
						var n = this,
							i = t(this._element).hasClass(d) ? d : "";
						if(this._isShown && this._config.backdrop) {
							if(this._backdrop = document.createElement("div"), this._backdrop.className = u, i && t(this._backdrop).addClass(i), t(this._backdrop).appendTo(document.body), t(this._element).on(c.CLICK_DISMISS, function(t) {
									n._ignoreBackdropClick ? n._ignoreBackdropClick = !1 : t.target === t.currentTarget && ("static" === n._config.backdrop ? n._element.focus() : n.hide())
								}), i && a.reflow(this._backdrop), t(this._backdrop).addClass(_), !e) return;
							if(!i) return void e();
							var s = a.getTransitionDurationFromElement(this._backdrop);
							t(this._backdrop).one(a.TRANSITION_END, e).emulateTransitionEnd(s)
						} else if(!this._isShown && this._backdrop) {
							t(this._backdrop).removeClass(_);
							var r = function() {
								n._removeBackdrop(), e && e()
							};
							if(t(this._element).hasClass(d)) {
								var o = a.getTransitionDurationFromElement(this._backdrop);
								t(this._backdrop).one(a.TRANSITION_END, r).emulateTransitionEnd(o)
							} else r()
						} else e && e()
					}, m._adjustDialog = function() {
						var t = this._element.scrollHeight > document.documentElement.clientHeight;
						!this._isBodyOverflowing && t && (this._element.style.paddingLeft = this._scrollbarWidth + "px"), this._isBodyOverflowing && !t && (this._element.style.paddingRight = this._scrollbarWidth + "px")
					}, m._resetAdjustments = function() {
						this._element.style.paddingLeft = "", this._element.style.paddingRight = ""
					}, m._checkScrollbar = function() {
						var t = document.body.getBoundingClientRect();
						this._isBodyOverflowing = t.left + t.right < window.innerWidth, this._scrollbarWidth = this._getScrollbarWidth()
					}, m._setScrollbar = function() {
						var e = this;
						if(this._isBodyOverflowing) {
							t(g.FIXED_CONTENT).each(function(n, i) {
								var s = t(i)[0].style.paddingRight,
									r = t(i).css("padding-right");
								t(i).data("padding-right", s).css("padding-right", parseFloat(r) + e._scrollbarWidth + "px")
							}), t(g.STICKY_CONTENT).each(function(n, i) {
								var s = t(i)[0].style.marginRight,
									r = t(i).css("margin-right");
								t(i).data("margin-right", s).css("margin-right", parseFloat(r) - e._scrollbarWidth + "px")
							}), t(g.NAVBAR_TOGGLER).each(function(n, i) {
								var s = t(i)[0].style.marginRight,
									r = t(i).css("margin-right");
								t(i).data("margin-right", s).css("margin-right", parseFloat(r) + e._scrollbarWidth + "px")
							});
							var n = document.body.style.paddingRight,
								i = t(document.body).css("padding-right");
							t(document.body).data("padding-right", n).css("padding-right", parseFloat(i) + this._scrollbarWidth + "px")
						}
					}, m._resetScrollbar = function() {
						t(g.FIXED_CONTENT).each(function(e, n) {
							var i = t(n).data("padding-right");
							void 0 !== i && t(n).css("padding-right", i).removeData("padding-right")
						}), t(g.STICKY_CONTENT + ", " + g.NAVBAR_TOGGLER).each(function(e, n) {
							var i = t(n).data("margin-right");
							void 0 !== i && t(n).css("margin-right", i).removeData("margin-right")
						});
						var e = t(document.body).data("padding-right");
						void 0 !== e && t(document.body).css("padding-right", e).removeData("padding-right")
					}, m._getScrollbarWidth = function() {
						var t = document.createElement("div");
						t.className = h, document.body.appendChild(t);
						var e = t.getBoundingClientRect().width - t.clientWidth;
						return document.body.removeChild(t), e
					}, i._jQueryInterface = function(e, n) {
						return this.each(function() {
							var s = t(this).data("bs.modal"),
								a = o({}, r, t(this).data(), "object" == typeof e && e ? e : {});
							if(s || (s = new i(this, a), t(this).data("bs.modal", s)), "string" == typeof e) {
								if(void 0 === s[e]) throw new TypeError('No method named "' + e + '"');
								s[e](n)
							} else a.show && s.show(n)
						})
					}, s(i, null, [{
						key: "VERSION",
						get: function() {
							return "4.1.1"
						}
					}, {
						key: "Default",
						get: function() {
							return r
						}
					}]), i
				}();
			return t(document).on(c.CLICK_DATA_API, g.DATA_TOGGLE, function(e) {
				var n, i = this,
					s = a.getSelectorFromElement(this);
				s && (n = t(s)[0]);
				var r = t(n).data("bs.modal") ? "toggle" : o({}, t(n).data(), t(this).data());
				"A" !== this.tagName && "AREA" !== this.tagName || e.preventDefault();
				var l = t(n).one(c.SHOW, function(e) {
					e.isDefaultPrevented() || l.one(c.HIDDEN, function() {
						t(i).is(":visible") && i.focus()
					})
				});
				m._jQueryInterface.call(t(n), r, this)
			}), t.fn.modal = m._jQueryInterface, t.fn.modal.Constructor = m, t.fn.modal.noConflict = function() {
				return t.fn.modal = i, m._jQueryInterface
			}, m
		}(e),
		_ = function(t) {
			var e = "tooltip",
				i = ".bs.tooltip",
				r = t.fn[e],
				l = new RegExp("(^|\\s)bs-tooltip\\S+", "g"),
				c = {
					animation: "boolean",
					template: "string",
					title: "(string|element|function)",
					trigger: "string",
					delay: "(number|object)",
					html: "boolean",
					selector: "(string|boolean)",
					placement: "(string|function)",
					offset: "(number|string)",
					container: "(string|element|boolean)",
					fallbackPlacement: "(string|array)",
					boundary: "(string|element)"
				},
				h = {
					AUTO: "auto",
					TOP: "top",
					RIGHT: "right",
					BOTTOM: "bottom",
					LEFT: "left"
				},
				u = {
					animation: !0,
					template: '<div class="tooltip" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>',
					trigger: "hover focus",
					title: "",
					delay: 0,
					html: !1,
					selector: !1,
					placement: "top",
					offset: 0,
					container: !1,
					fallbackPlacement: "flip",
					boundary: "scrollParent"
				},
				f = "show",
				d = "out",
				_ = {
					HIDE: "hide" + i,
					HIDDEN: "hidden" + i,
					SHOW: "show" + i,
					SHOWN: "shown" + i,
					INSERTED: "inserted" + i,
					CLICK: "click" + i,
					FOCUSIN: "focusin" + i,
					FOCUSOUT: "focusout" + i,
					MOUSEENTER: "mouseenter" + i,
					MOUSELEAVE: "mouseleave" + i
				},
				g = "fade",
				m = "show",
				p = ".tooltip-inner",
				v = ".arrow",
				E = "hover",
				T = "focus",
				b = "click",
				y = "manual",
				C = function() {
					function r(t, e) {
						if(void 0 === n) throw new TypeError("Bootstrap tooltips require Popper.js (https://popper.js.org)");
						this._isEnabled = !0, this._timeout = 0, this._hoverState = "", this._activeTrigger = {}, this._popper = null, this.element = t, this.config = this._getConfig(e), this.tip = null, this._setListeners()
					}
					var C = r.prototype;
					return C.enable = function() {
						this._isEnabled = !0
					}, C.disable = function() {
						this._isEnabled = !1
					}, C.toggleEnabled = function() {
						this._isEnabled = !this._isEnabled
					}, C.toggle = function(e) {
						if(this._isEnabled)
							if(e) {
								var n = this.constructor.DATA_KEY,
									i = t(e.currentTarget).data(n);
								i || (i = new this.constructor(e.currentTarget, this._getDelegateConfig()), t(e.currentTarget).data(n, i)), i._activeTrigger.click = !i._activeTrigger.click, i._isWithActiveTrigger() ? i._enter(null, i) : i._leave(null, i)
							} else {
								if(t(this.getTipElement()).hasClass(m)) return void this._leave(null, this);
								this._enter(null, this)
							}
					}, C.dispose = function() {
						clearTimeout(this._timeout), t.removeData(this.element, this.constructor.DATA_KEY), t(this.element).off(this.constructor.EVENT_KEY), t(this.element).closest(".modal").off("hide.bs.modal"), this.tip && t(this.tip).remove(), this._isEnabled = null, this._timeout = null, this._hoverState = null, this._activeTrigger = null, null !== this._popper && this._popper.destroy(), this._popper = null, this.element = null, this.config = null, this.tip = null
					}, C.show = function() {
						var e = this;
						if("none" === t(this.element).css("display")) throw new Error("Please use show on visible elements");
						var i = t.Event(this.constructor.Event.SHOW);
						if(this.isWithContent() && this._isEnabled) {
							t(this.element).trigger(i);
							var s = t.contains(this.element.ownerDocument.documentElement, this.element);
							if(i.isDefaultPrevented() || !s) return;
							var r = this.getTipElement(),
								o = a.getUID(this.constructor.NAME);
							r.setAttribute("id", o), this.element.setAttribute("aria-describedby", o), this.setContent(), this.config.animation && t(r).addClass(g);
							var l = "function" == typeof this.config.placement ? this.config.placement.call(this, r, this.element) : this.config.placement,
								c = this._getAttachment(l);
							this.addAttachmentClass(c);
							var h = !1 === this.config.container ? document.body : t(this.config.container);
							t(r).data(this.constructor.DATA_KEY, this), t.contains(this.element.ownerDocument.documentElement, this.tip) || t(r).appendTo(h), t(this.element).trigger(this.constructor.Event.INSERTED), this._popper = new n(this.element, r, {
								placement: c,
								modifiers: {
									offset: {
										offset: this.config.offset
									},
									flip: {
										behavior: this.config.fallbackPlacement
									},
									arrow: {
										element: v
									},
									preventOverflow: {
										boundariesElement: this.config.boundary
									}
								},
								onCreate: function(t) {
									t.originalPlacement !== t.placement && e._handlePopperPlacementChange(t)
								},
								onUpdate: function(t) {
									e._handlePopperPlacementChange(t)
								}
							}), t(r).addClass(m), "ontouchstart" in document.documentElement && t(document.body).children().on("mouseover", null, t.noop);
							var u = function() {
								e.config.animation && e._fixTransition();
								var n = e._hoverState;
								e._hoverState = null, t(e.element).trigger(e.constructor.Event.SHOWN), n === d && e._leave(null, e)
							};
							if(t(this.tip).hasClass(g)) {
								var f = a.getTransitionDurationFromElement(this.tip);
								t(this.tip).one(a.TRANSITION_END, u).emulateTransitionEnd(f)
							} else u()
						}
					}, C.hide = function(e) {
						var n = this,
							i = this.getTipElement(),
							s = t.Event(this.constructor.Event.HIDE),
							r = function() {
								n._hoverState !== f && i.parentNode && i.parentNode.removeChild(i), n._cleanTipClass(), n.element.removeAttribute("aria-describedby"), t(n.element).trigger(n.constructor.Event.HIDDEN), null !== n._popper && n._popper.destroy(), e && e()
							};
						if(t(this.element).trigger(s), !s.isDefaultPrevented()) {
							if(t(i).removeClass(m), "ontouchstart" in document.documentElement && t(document.body).children().off("mouseover", null, t.noop), this._activeTrigger[b] = !1, this._activeTrigger[T] = !1, this._activeTrigger[E] = !1, t(this.tip).hasClass(g)) {
								var o = a.getTransitionDurationFromElement(i);
								t(i).one(a.TRANSITION_END, r).emulateTransitionEnd(o)
							} else r();
							this._hoverState = ""
						}
					}, C.update = function() {
						null !== this._popper && this._popper.scheduleUpdate()
					}, C.isWithContent = function() {
						return Boolean(this.getTitle())
					}, C.addAttachmentClass = function(e) {
						t(this.getTipElement()).addClass("bs-tooltip-" + e)
					}, C.getTipElement = function() {
						return this.tip = this.tip || t(this.config.template)[0], this.tip
					}, C.setContent = function() {
						var e = t(this.getTipElement());
						this.setElementContent(e.find(p), this.getTitle()), e.removeClass(g + " " + m)
					}, C.setElementContent = function(e, n) {
						var i = this.config.html;
						"object" == typeof n && (n.nodeType || n.jquery) ? i ? t(n).parent().is(e) || e.empty().append(n) : e.text(t(n).text()) : e[i ? "html" : "text"](n)
					}, C.getTitle = function() {
						var t = this.element.getAttribute("data-original-title");
						return t || (t = "function" == typeof this.config.title ? this.config.title.call(this.element) : this.config.title), t
					}, C._getAttachment = function(t) {
						return h[t.toUpperCase()]
					}, C._setListeners = function() {
						var e = this;
						this.config.trigger.split(" ").forEach(function(n) {
							if("click" === n) t(e.element).on(e.constructor.Event.CLICK, e.config.selector, function(t) {
								return e.toggle(t)
							});
							else if(n !== y) {
								var i = n === E ? e.constructor.Event.MOUSEENTER : e.constructor.Event.FOCUSIN,
									s = n === E ? e.constructor.Event.MOUSELEAVE : e.constructor.Event.FOCUSOUT;
								t(e.element).on(i, e.config.selector, function(t) {
									return e._enter(t)
								}).on(s, e.config.selector, function(t) {
									return e._leave(t)
								})
							}
							t(e.element).closest(".modal").on("hide.bs.modal", function() {
								return e.hide()
							})
						}), this.config.selector ? this.config = o({}, this.config, {
							trigger: "manual",
							selector: ""
						}) : this._fixTitle()
					}, C._fixTitle = function() {
						var t = typeof this.element.getAttribute("data-original-title");
						(this.element.getAttribute("title") || "string" !== t) && (this.element.setAttribute("data-original-title", this.element.getAttribute("title") || ""), this.element.setAttribute("title", ""))
					}, C._enter = function(e, n) {
						var i = this.constructor.DATA_KEY;
						(n = n || t(e.currentTarget).data(i)) || (n = new this.constructor(e.currentTarget, this._getDelegateConfig()), t(e.currentTarget).data(i, n)), e && (n._activeTrigger["focusin" === e.type ? T : E] = !0), t(n.getTipElement()).hasClass(m) || n._hoverState === f ? n._hoverState = f : (clearTimeout(n._timeout), n._hoverState = f, n.config.delay && n.config.delay.show ? n._timeout = setTimeout(function() {
							n._hoverState === f && n.show()
						}, n.config.delay.show) : n.show())
					}, C._leave = function(e, n) {
						var i = this.constructor.DATA_KEY;
						(n = n || t(e.currentTarget).data(i)) || (n = new this.constructor(e.currentTarget, this._getDelegateConfig()), t(e.currentTarget).data(i, n)), e && (n._activeTrigger["focusout" === e.type ? T : E] = !1), n._isWithActiveTrigger() || (clearTimeout(n._timeout), n._hoverState = d, n.config.delay && n.config.delay.hide ? n._timeout = setTimeout(function() {
							n._hoverState === d && n.hide()
						}, n.config.delay.hide) : n.hide())
					}, C._isWithActiveTrigger = function() {
						for(var t in this._activeTrigger)
							if(this._activeTrigger[t]) return !0;
						return !1
					}, C._getConfig = function(n) {
						return "number" == typeof(n = o({}, this.constructor.Default, t(this.element).data(), "object" == typeof n && n ? n : {})).delay && (n.delay = {
							show: n.delay,
							hide: n.delay
						}), "number" == typeof n.title && (n.title = n.title.toString()), "number" == typeof n.content && (n.content = n.content.toString()), a.typeCheckConfig(e, n, this.constructor.DefaultType), n
					}, C._getDelegateConfig = function() {
						var t = {};
						if(this.config)
							for(var e in this.config) this.constructor.Default[e] !== this.config[e] && (t[e] = this.config[e]);
						return t
					}, C._cleanTipClass = function() {
						var e = t(this.getTipElement()),
							n = e.attr("class").match(l);
						null !== n && n.length > 0 && e.removeClass(n.join(""))
					}, C._handlePopperPlacementChange = function(t) {
						this._cleanTipClass(), this.addAttachmentClass(this._getAttachment(t.placement))
					}, C._fixTransition = function() {
						var e = this.getTipElement(),
							n = this.config.animation;
						null === e.getAttribute("x-placement") && (t(e).removeClass(g), this.config.animation = !1, this.hide(), this.show(), this.config.animation = n)
					}, r._jQueryInterface = function(e) {
						return this.each(function() {
							var n = t(this).data("bs.tooltip"),
								i = "object" == typeof e && e;
							if((n || !/dispose|hide/.test(e)) && (n || (n = new r(this, i), t(this).data("bs.tooltip", n)), "string" == typeof e)) {
								if(void 0 === n[e]) throw new TypeError('No method named "' + e + '"');
								n[e]()
							}
						})
					}, s(r, null, [{
						key: "VERSION",
						get: function() {
							return "4.1.1"
						}
					}, {
						key: "Default",
						get: function() {
							return u
						}
					}, {
						key: "NAME",
						get: function() {
							return e
						}
					}, {
						key: "DATA_KEY",
						get: function() {
							return "bs.tooltip"
						}
					}, {
						key: "Event",
						get: function() {
							return _
						}
					}, {
						key: "EVENT_KEY",
						get: function() {
							return i
						}
					}, {
						key: "DefaultType",
						get: function() {
							return c
						}
					}]), r
				}();
			return t.fn[e] = C._jQueryInterface, t.fn[e].Constructor = C, t.fn[e].noConflict = function() {
				return t.fn[e] = r, C._jQueryInterface
			}, C
		}(e),
		g = function(t) {
			var e = "popover",
				n = ".bs.popover",
				i = t.fn[e],
				r = new RegExp("(^|\\s)bs-popover\\S+", "g"),
				a = o({}, _.Default, {
					placement: "right",
					trigger: "click",
					content: "",
					template: '<div class="popover" role="tooltip"><div class="arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>'
				}),
				l = o({}, _.DefaultType, {
					content: "(string|element|function)"
				}),
				c = "fade",
				h = "show",
				u = ".popover-header",
				f = ".popover-body",
				d = {
					HIDE: "hide" + n,
					HIDDEN: "hidden" + n,
					SHOW: "show" + n,
					SHOWN: "shown" + n,
					INSERTED: "inserted" + n,
					CLICK: "click" + n,
					FOCUSIN: "focusin" + n,
					FOCUSOUT: "focusout" + n,
					MOUSEENTER: "mouseenter" + n,
					MOUSELEAVE: "mouseleave" + n
				},
				g = function(i) {
					var o, _;

					function g() {
						return i.apply(this, arguments) || this
					}
					_ = i, (o = g).prototype = Object.create(_.prototype), o.prototype.constructor = o, o.__proto__ = _;
					var m = g.prototype;
					return m.isWithContent = function() {
						return this.getTitle() || this._getContent()
					}, m.addAttachmentClass = function(e) {
						t(this.getTipElement()).addClass("bs-popover-" + e)
					}, m.getTipElement = function() {
						return this.tip = this.tip || t(this.config.template)[0], this.tip
					}, m.setContent = function() {
						var e = t(this.getTipElement());
						this.setElementContent(e.find(u), this.getTitle());
						var n = this._getContent();
						"function" == typeof n && (n = n.call(this.element)), this.setElementContent(e.find(f), n), e.removeClass(c + " " + h)
					}, m._getContent = function() {
						return this.element.getAttribute("data-content") || this.config.content
					}, m._cleanTipClass = function() {
						var e = t(this.getTipElement()),
							n = e.attr("class").match(r);
						null !== n && n.length > 0 && e.removeClass(n.join(""))
					}, g._jQueryInterface = function(e) {
						return this.each(function() {
							var n = t(this).data("bs.popover"),
								i = "object" == typeof e ? e : null;
							if((n || !/destroy|hide/.test(e)) && (n || (n = new g(this, i), t(this).data("bs.popover", n)), "string" == typeof e)) {
								if(void 0 === n[e]) throw new TypeError('No method named "' + e + '"');
								n[e]()
							}
						})
					}, s(g, null, [{
						key: "VERSION",
						get: function() {
							return "4.1.1"
						}
					}, {
						key: "Default",
						get: function() {
							return a
						}
					}, {
						key: "NAME",
						get: function() {
							return e
						}
					}, {
						key: "DATA_KEY",
						get: function() {
							return "bs.popover"
						}
					}, {
						key: "Event",
						get: function() {
							return d
						}
					}, {
						key: "EVENT_KEY",
						get: function() {
							return n
						}
					}, {
						key: "DefaultType",
						get: function() {
							return l
						}
					}]), g
				}(_);
			return t.fn[e] = g._jQueryInterface, t.fn[e].Constructor = g, t.fn[e].noConflict = function() {
				return t.fn[e] = i, g._jQueryInterface
			}, g
		}(e),
		m = function(t) {
			var e = "scrollspy",
				n = t.fn[e],
				i = {
					offset: 10,
					method: "auto",
					target: ""
				},
				r = {
					offset: "number",
					method: "string",
					target: "(string|element)"
				},
				l = {
					ACTIVATE: "activate.bs.scrollspy",
					SCROLL: "scroll.bs.scrollspy",
					LOAD_DATA_API: "load.bs.scrollspy.data-api"
				},
				c = "dropdown-item",
				h = "active",
				u = {
					DATA_SPY: '[data-spy="scroll"]',
					ACTIVE: ".active",
					NAV_LIST_GROUP: ".nav, .list-group",
					NAV_LINKS: ".nav-link",
					NAV_ITEMS: ".nav-item",
					LIST_ITEMS: ".list-group-item",
					DROPDOWN: ".dropdown",
					DROPDOWN_ITEMS: ".dropdown-item",
					DROPDOWN_TOGGLE: ".dropdown-toggle"
				},
				f = "offset",
				d = "position",
				_ = function() {
					function n(e, n) {
						var i = this;
						this._element = e, this._scrollElement = "BODY" === e.tagName ? window : e, this._config = this._getConfig(n), this._selector = this._config.target + " " + u.NAV_LINKS + "," + this._config.target + " " + u.LIST_ITEMS + "," + this._config.target + " " + u.DROPDOWN_ITEMS, this._offsets = [], this._targets = [], this._activeTarget = null, this._scrollHeight = 0, t(this._scrollElement).on(l.SCROLL, function(t) {
							return i._process(t)
						}), this.refresh(), this._process()
					}
					var _ = n.prototype;
					return _.refresh = function() {
						var e = this,
							n = this._scrollElement === this._scrollElement.window ? f : d,
							i = "auto" === this._config.method ? n : this._config.method,
							s = i === d ? this._getScrollTop() : 0;
						this._offsets = [], this._targets = [], this._scrollHeight = this._getScrollHeight(), t.makeArray(t(this._selector)).map(function(e) {
							var n, r = a.getSelectorFromElement(e);
							if(r && (n = t(r)[0]), n) {
								var o = n.getBoundingClientRect();
								if(o.width || o.height) return [t(n)[i]().top + s, r]
							}
							return null
						}).filter(function(t) {
							return t
						}).sort(function(t, e) {
							return t[0] - e[0]
						}).forEach(function(t) {
							e._offsets.push(t[0]), e._targets.push(t[1])
						})
					}, _.dispose = function() {
						t.removeData(this._element, "bs.scrollspy"), t(this._scrollElement).off(".bs.scrollspy"), this._element = null, this._scrollElement = null, this._config = null, this._selector = null, this._offsets = null, this._targets = null, this._activeTarget = null, this._scrollHeight = null
					}, _._getConfig = function(n) {
						if("string" != typeof(n = o({}, i, "object" == typeof n && n ? n : {})).target) {
							var s = t(n.target).attr("id");
							s || (s = a.getUID(e), t(n.target).attr("id", s)), n.target = "#" + s
						}
						return a.typeCheckConfig(e, n, r), n
					}, _._getScrollTop = function() {
						return this._scrollElement === window ? this._scrollElement.pageYOffset : this._scrollElement.scrollTop
					}, _._getScrollHeight = function() {
						return this._scrollElement.scrollHeight || Math.max(document.body.scrollHeight, document.documentElement.scrollHeight)
					}, _._getOffsetHeight = function() {
						return this._scrollElement === window ? window.innerHeight : this._scrollElement.getBoundingClientRect().height
					}, _._process = function() {
						var t = this._getScrollTop() + this._config.offset,
							e = this._getScrollHeight(),
							n = this._config.offset + e - this._getOffsetHeight();
						if(this._scrollHeight !== e && this.refresh(), t >= n) {
							var i = this._targets[this._targets.length - 1];
							this._activeTarget !== i && this._activate(i)
						} else {
							if(this._activeTarget && t < this._offsets[0] && this._offsets[0] > 0) return this._activeTarget = null, void this._clear();
							for(var s = this._offsets.length; s--;) {
								this._activeTarget !== this._targets[s] && t >= this._offsets[s] && (void 0 === this._offsets[s + 1] || t < this._offsets[s + 1]) && this._activate(this._targets[s])
							}
						}
					}, _._activate = function(e) {
						this._activeTarget = e, this._clear();
						var n = this._selector.split(",");
						n = n.map(function(t) {
							return t + '[data-target="' + e + '"],' + t + '[href="' + e + '"]'
						});
						var i = t(n.join(","));
						i.hasClass(c) ? (i.closest(u.DROPDOWN).find(u.DROPDOWN_TOGGLE).addClass(h), i.addClass(h)) : (i.addClass(h), i.parents(u.NAV_LIST_GROUP).prev(u.NAV_LINKS + ", " + u.LIST_ITEMS).addClass(h), i.parents(u.NAV_LIST_GROUP).prev(u.NAV_ITEMS).children(u.NAV_LINKS).addClass(h)), t(this._scrollElement).trigger(l.ACTIVATE, {
							relatedTarget: e
						})
					}, _._clear = function() {
						t(this._selector).filter(u.ACTIVE).removeClass(h)
					}, n._jQueryInterface = function(e) {
						return this.each(function() {
							var i = t(this).data("bs.scrollspy");
							if(i || (i = new n(this, "object" == typeof e && e), t(this).data("bs.scrollspy", i)), "string" == typeof e) {
								if(void 0 === i[e]) throw new TypeError('No method named "' + e + '"');
								i[e]()
							}
						})
					}, s(n, null, [{
						key: "VERSION",
						get: function() {
							return "4.1.1"
						}
					}, {
						key: "Default",
						get: function() {
							return i
						}
					}]), n
				}();
			return t(window).on(l.LOAD_DATA_API, function() {
				for(var e = t.makeArray(t(u.DATA_SPY)), n = e.length; n--;) {
					var i = t(e[n]);
					_._jQueryInterface.call(i, i.data())
				}
			}), t.fn[e] = _._jQueryInterface, t.fn[e].Constructor = _, t.fn[e].noConflict = function() {
				return t.fn[e] = n, _._jQueryInterface
			}, _
		}(e),
		p = function(t) {
			var e = t.fn.tab,
				n = {
					HIDE: "hide.bs.tab",
					HIDDEN: "hidden.bs.tab",
					SHOW: "show.bs.tab",
					SHOWN: "shown.bs.tab",
					CLICK_DATA_API: "click.bs.tab.data-api"
				},
				i = "dropdown-menu",
				r = "active",
				o = "disabled",
				l = "fade",
				c = "show",
				h = ".dropdown",
				u = ".nav, .list-group",
				f = ".active",
				d = "> li > .active",
				_ = '[data-toggle="tab"], [data-toggle="pill"], [data-toggle="list"]',
				g = ".dropdown-toggle",
				m = "> .dropdown-menu .active",
				p = function() {
					function e(t) {
						this._element = t
					}
					var _ = e.prototype;
					return _.show = function() {
						var e = this;
						if(!(this._element.parentNode && this._element.parentNode.nodeType === Node.ELEMENT_NODE && t(this._element).hasClass(r) || t(this._element).hasClass(o))) {
							var i, s, l = t(this._element).closest(u)[0],
								c = a.getSelectorFromElement(this._element);
							if(l) {
								var h = "UL" === l.nodeName ? d : f;
								s = (s = t.makeArray(t(l).find(h)))[s.length - 1]
							}
							var _ = t.Event(n.HIDE, {
									relatedTarget: this._element
								}),
								g = t.Event(n.SHOW, {
									relatedTarget: s
								});
							if(s && t(s).trigger(_), t(this._element).trigger(g), !g.isDefaultPrevented() && !_.isDefaultPrevented()) {
								c && (i = t(c)[0]), this._activate(this._element, l);
								var m = function() {
									var i = t.Event(n.HIDDEN, {
											relatedTarget: e._element
										}),
										r = t.Event(n.SHOWN, {
											relatedTarget: s
										});
									t(s).trigger(i), t(e._element).trigger(r)
								};
								i ? this._activate(i, i.parentNode, m) : m()
							}
						}
					}, _.dispose = function() {
						t.removeData(this._element, "bs.tab"), this._element = null
					}, _._activate = function(e, n, i) {
						var s = this,
							r = ("UL" === n.nodeName ? t(n).find(d) : t(n).children(f))[0],
							o = i && r && t(r).hasClass(l),
							c = function() {
								return s._transitionComplete(e, r, i)
							};
						if(r && o) {
							var h = a.getTransitionDurationFromElement(r);
							t(r).one(a.TRANSITION_END, c).emulateTransitionEnd(h)
						} else c()
					}, _._transitionComplete = function(e, n, s) {
						if(n) {
							t(n).removeClass(c + " " + r);
							var o = t(n.parentNode).find(m)[0];
							o && t(o).removeClass(r), "tab" === n.getAttribute("role") && n.setAttribute("aria-selected", !1)
						}
						if(t(e).addClass(r), "tab" === e.getAttribute("role") && e.setAttribute("aria-selected", !0), a.reflow(e), t(e).addClass(c), e.parentNode && t(e.parentNode).hasClass(i)) {
							var l = t(e).closest(h)[0];
							l && t(l).find(g).addClass(r), e.setAttribute("aria-expanded", !0)
						}
						s && s()
					}, e._jQueryInterface = function(n) {
						return this.each(function() {
							var i = t(this),
								s = i.data("bs.tab");
							if(s || (s = new e(this), i.data("bs.tab", s)), "string" == typeof n) {
								if(void 0 === s[n]) throw new TypeError('No method named "' + n + '"');
								s[n]()
							}
						})
					}, s(e, null, [{
						key: "VERSION",
						get: function() {
							return "4.1.1"
						}
					}]), e
				}();
			return t(document).on(n.CLICK_DATA_API, _, function(e) {
				e.preventDefault(), p._jQueryInterface.call(t(this), "show")
			}), t.fn.tab = p._jQueryInterface, t.fn.tab.Constructor = p, t.fn.tab.noConflict = function() {
				return t.fn.tab = e, p._jQueryInterface
			}, p
		}(e);
	! function(t) {
		if(void 0 === t) throw new TypeError("Bootstrap's JavaScript requires jQuery. jQuery must be included before Bootstrap's JavaScript.");
		var e = t.fn.jquery.split(" ")[0].split(".");
		if(e[0] < 2 && e[1] < 9 || 1 === e[0] && 9 === e[1] && e[2] < 1 || e[0] >= 4) throw new Error("Bootstrap's JavaScript requires at least jQuery v1.9.1 but less than v4.0.0")
	}(e), t.Util = a, t.Alert = l, t.Button = c, t.Carousel = h, t.Collapse = u, t.Dropdown = f, t.Modal = d, t.Popover = g, t.Scrollspy = m, t.Tab = p, t.Tooltip = _, Object.defineProperty(t, "__esModule", {
		value: !0
	})
});
// JQuery Elevatezoom Start Added From Here
"function" != typeof Object.create && (Object.create = function(o) {
		function i() {}
		return i.prototype = o, new i
	}),
	function(o, i, t, e) {
		var n = {
			init: function(i, t) {
				var e = this;
				e.elem = t, e.$elem = o(t), e.imageSrc = e.$elem.data("zoom-image") ? e.$elem.data("zoom-image") : e.$elem.attr("src"), e.options = o.extend({}, o.fn.elevateZoom.options, i), e.options.tint && (e.options.lensColour = "none", e.options.lensOpacity = "1"), "inner" == e.options.zoomType && (e.options.showLens = !1), e.$elem.parent().removeAttr("title").removeAttr("alt"), e.zoomImage = e.imageSrc, e.refresh(1), o("#" + e.options.gallery + " a").click(function(i) {
					return e.options.galleryActiveClass && (o("#" + e.options.gallery + " a").removeClass(e.options.galleryActiveClass), o(this).addClass(e.options.galleryActiveClass)), i.preventDefault(), o(this).data("zoom-image") ? e.zoomImagePre = o(this).data("zoom-image") : e.zoomImagePre = o(this).data("image"), e.swaptheimage(o(this).data("image"), e.zoomImagePre), !1
				})
			},
			refresh: function(o) {
				var i = this;
				setTimeout(function() {
					i.fetch(i.imageSrc)
				}, o || i.options.refresh)
			},
			fetch: function(o) {
				var i = this,
					t = new Image;
				t.onload = function() {
					i.largeWidth = t.width, i.largeHeight = t.height, i.startZoom(), i.currentImage = i.imageSrc, i.options.onZoomedImageLoaded(i.$elem)
				}, t.src = o
			},
			startZoom: function() {
				var i = this;
				if(i.nzWidth = i.$elem.width(), i.nzHeight = i.$elem.height(), i.isWindowActive = !1, i.isLensActive = !1, i.isTintActive = !1, i.overWindow = !1, i.options.imageCrossfade && (i.zoomWrap = i.$elem.wrap('<div style="height:' + i.nzHeight + "px;width:" + i.nzWidth + 'px;" class="zoomWrapper" />'), i.$elem.css("position", "absolute")), i.zoomLock = 1, i.scrollingLock = !1, i.changeBgSize = !1, i.currentZoomLevel = i.options.zoomLevel, i.nzOffset = i.$elem.offset(), i.widthRatio = i.largeWidth / i.currentZoomLevel / i.nzWidth, i.heightRatio = i.largeHeight / i.currentZoomLevel / i.nzHeight, "window" == i.options.zoomType && (i.zoomWindowStyle = "overflow: hidden;background-position: 0px 0px;text-align:center;background-color: " + String(i.options.zoomWindowBgColour) + ";width: " + String(i.options.zoomWindowWidth) + "px;height: " + String(i.options.zoomWindowHeight) + "px;float: left;background-size: " + i.largeWidth / i.currentZoomLevel + "px " + i.largeHeight / i.currentZoomLevel + "px;display: none;z-index:100;border: " + String(i.options.borderSize) + "px solid " + i.options.borderColour + ";background-repeat: no-repeat;position: absolute;"), "inner" == i.options.zoomType) {
					var t = i.$elem.css("border-left-width");
					i.zoomWindowStyle = "overflow: hidden;margin-left: " + String(t) + ";margin-top: " + String(t) + ";background-position: 0px 0px;width: " + String(i.nzWidth) + "px;height: " + String(i.nzHeight) + "px;px;float: left;display: none;cursor:" + i.options.cursor + ";px solid " + i.options.borderColour + ";background-repeat: no-repeat;position: absolute;"
				}
				"window" == i.options.zoomType && (i.nzHeight < i.options.zoomWindowWidth / i.widthRatio ? lensHeight = i.nzHeight : lensHeight = String(i.options.zoomWindowHeight / i.heightRatio), i.largeWidth < i.options.zoomWindowWidth ? lensWidth = i.nzWidth : lensWidth = i.options.zoomWindowWidth / i.widthRatio, i.lensStyle = "background-position: 0px 0px;width: " + String(i.options.zoomWindowWidth / i.widthRatio) + "px;height: " + String(i.options.zoomWindowHeight / i.heightRatio) + "px;float: right;display: none;overflow: hidden;z-index: 999;-webkit-transform: translateZ(0);opacity:" + i.options.lensOpacity + ";filter: alpha(opacity = " + 100 * i.options.lensOpacity + "); zoom:1;width:" + lensWidth + "px;height:" + lensHeight + "px;background-color:" + i.options.lensColour + ";cursor:" + i.options.cursor + ";border: " + i.options.lensBorderSize + "px solid " + i.options.lensBorderColour + ";background-repeat: no-repeat;position: absolute;"), i.tintStyle = "display: block;position: absolute;background-color: " + i.options.tintColour + ";filter:alpha(opacity=0);opacity: 0;width: " + i.nzWidth + "px;height: " + i.nzHeight + "px;", i.lensRound = "", "lens" == i.options.zoomType && (i.lensStyle = "background-position: 0px 0px;float: left;display: none;border: " + String(i.options.borderSize) + "px solid " + i.options.borderColour + ";width:" + String(i.options.lensSize) + "px;height:" + String(i.options.lensSize) + "px;background-repeat: no-repeat;position: absolute;"), "round" == i.options.lensShape && (i.lensRound = "border-top-left-radius: " + String(i.options.lensSize / 2 + i.options.borderSize) + "px;border-top-right-radius: " + String(i.options.lensSize / 2 + i.options.borderSize) + "px;border-bottom-left-radius: " + String(i.options.lensSize / 2 + i.options.borderSize) + "px;border-bottom-right-radius: " + String(i.options.lensSize / 2 + i.options.borderSize) + "px;"), i.zoomContainer = o('<div class="zoomContainer" style="-webkit-transform: translateZ(0);position:absolute;left:' + i.nzOffset.left + "px;top:" + i.nzOffset.top + "px;height:" + i.nzHeight + "px;width:" + i.nzWidth + 'px;"></div>'), o("body").append(i.zoomContainer), i.options.containLensZoom && "lens" == i.options.zoomType && i.zoomContainer.css("overflow", "hidden"), "inner" != i.options.zoomType && (i.zoomLens = o("<div class='zoomLens' style='" + i.lensStyle + i.lensRound + "'>&nbsp;</div>").appendTo(i.zoomContainer).click(function() {
					i.$elem.trigger("click")
				}), i.options.tint && (i.tintContainer = o("<div/>").addClass("tintContainer"), i.zoomTint = o("<div class='zoomTint' style='" + i.tintStyle + "'></div>"), i.zoomLens.wrap(i.tintContainer), i.zoomTintcss = i.zoomLens.after(i.zoomTint), i.zoomTintImage = o('<img style="position: absolute; left: 0px; top: 0px; max-width: none; width: ' + i.nzWidth + "px; height: " + i.nzHeight + 'px;" src="' + i.imageSrc + '">').appendTo(i.zoomLens).click(function() {
					i.$elem.trigger("click")
				}))), isNaN(i.options.zoomWindowPosition) ? i.zoomWindow = o("<div style='z-index:999;left:" + i.windowOffsetLeft + "px;top:" + i.windowOffsetTop + "px;" + i.zoomWindowStyle + "' class='zoomWindow'>&nbsp;</div>").appendTo("body").click(function() {
					i.$elem.trigger("click")
				}) : i.zoomWindow = o("<div style='z-index:999;left:" + i.windowOffsetLeft + "px;top:" + i.windowOffsetTop + "px;" + i.zoomWindowStyle + "' class='zoomWindow'>&nbsp;</div>").appendTo(i.zoomContainer).click(function() {
					i.$elem.trigger("click")
				}), i.zoomWindowContainer = o("<div/>").addClass("zoomWindowContainer").css("width", i.options.zoomWindowWidth), i.zoomWindow.wrap(i.zoomWindowContainer), "lens" == i.options.zoomType && i.zoomLens.css({
					backgroundImage: "url('" + i.imageSrc + "')"
				}), "window" == i.options.zoomType && i.zoomWindow.css({
					backgroundImage: "url('" + i.imageSrc + "')"
				}), "inner" == i.options.zoomType && i.zoomWindow.css({
					backgroundImage: "url('" + i.imageSrc + "')"
				}), i.$elem.bind("touchmove", function(o) {
					o.preventDefault();
					var t = o.originalEvent.touches[0] || o.originalEvent.changedTouches[0];
					i.setPosition(t)
				}), i.zoomContainer.bind("touchmove", function(o) {
					"inner" == i.options.zoomType && i.showHideWindow("show"), o.preventDefault();
					var t = o.originalEvent.touches[0] || o.originalEvent.changedTouches[0];
					i.setPosition(t)
				}), i.zoomContainer.bind("touchend", function(o) {
					i.showHideWindow("hide"), i.options.showLens && i.showHideLens("hide"), i.options.tint && "inner" != i.options.zoomType && i.showHideTint("hide")
				}), i.$elem.bind("touchend", function(o) {
					i.showHideWindow("hide"), i.options.showLens && i.showHideLens("hide"), i.options.tint && "inner" != i.options.zoomType && i.showHideTint("hide")
				}), i.options.showLens && (i.zoomLens.bind("touchmove", function(o) {
					o.preventDefault();
					var t = o.originalEvent.touches[0] || o.originalEvent.changedTouches[0];
					i.setPosition(t)
				}), i.zoomLens.bind("touchend", function(o) {
					i.showHideWindow("hide"), i.options.showLens && i.showHideLens("hide"), i.options.tint && "inner" != i.options.zoomType && i.showHideTint("hide")
				})), i.$elem.bind("mousemove", function(o) {
					0 == i.overWindow && i.setElements("show"), i.lastX === o.clientX && i.lastY === o.clientY || (i.setPosition(o), i.currentLoc = o), i.lastX = o.clientX, i.lastY = o.clientY
				}), i.zoomContainer.bind("mousemove", function(o) {
					0 == i.overWindow && i.setElements("show"), i.lastX === o.clientX && i.lastY === o.clientY || (i.setPosition(o), i.currentLoc = o), i.lastX = o.clientX, i.lastY = o.clientY
				}), "inner" != i.options.zoomType && i.zoomLens.bind("mousemove", function(o) {
					i.lastX === o.clientX && i.lastY === o.clientY || (i.setPosition(o), i.currentLoc = o), i.lastX = o.clientX, i.lastY = o.clientY
				}), i.options.tint && "inner" != i.options.zoomType && i.zoomTint.bind("mousemove", function(o) {
					i.lastX === o.clientX && i.lastY === o.clientY || (i.setPosition(o), i.currentLoc = o), i.lastX = o.clientX, i.lastY = o.clientY
				}), "inner" == i.options.zoomType && i.zoomWindow.bind("mousemove", function(o) {
					i.lastX === o.clientX && i.lastY === o.clientY || (i.setPosition(o), i.currentLoc = o), i.lastX = o.clientX, i.lastY = o.clientY
				}), i.zoomContainer.add(i.$elem).mouseenter(function() {
					0 == i.overWindow && i.setElements("show")
				}).mouseleave(function() {
					i.scrollLock || (i.setElements("hide"), i.options.onDestroy(i.$elem))
				}), "inner" != i.options.zoomType && i.zoomWindow.mouseenter(function() {
					i.overWindow = !0, i.setElements("hide")
				}).mouseleave(function() {
					i.overWindow = !1
				}), i.options.zoomLevel, i.options.minZoomLevel ? i.minZoomLevel = i.options.minZoomLevel : i.minZoomLevel = 2 * i.options.scrollZoomIncrement, i.options.scrollZoom && i.zoomContainer.add(i.$elem).bind("mousewheel DOMMouseScroll MozMousePixelScroll", function(t) {
					i.scrollLock = !0, clearTimeout(o.data(this, "timer")), o.data(this, "timer", setTimeout(function() {
						i.scrollLock = !1
					}, 250));
					var e = t.originalEvent.wheelDelta || -1 * t.originalEvent.detail;
					return t.stopImmediatePropagation(), t.stopPropagation(), t.preventDefault(), e / 120 > 0 ? i.currentZoomLevel >= i.minZoomLevel && i.changeZoomLevel(i.currentZoomLevel - i.options.scrollZoomIncrement) : i.options.maxZoomLevel ? i.currentZoomLevel <= i.options.maxZoomLevel && i.changeZoomLevel(parseFloat(i.currentZoomLevel) + i.options.scrollZoomIncrement) : i.changeZoomLevel(parseFloat(i.currentZoomLevel) + i.options.scrollZoomIncrement), !1
				})
			},
			setElements: function(o) {
				if(!this.options.zoomEnabled) return !1;
				"show" == o && this.isWindowSet && ("inner" == this.options.zoomType && this.showHideWindow("show"), "window" == this.options.zoomType && this.showHideWindow("show"), this.options.showLens && this.showHideLens("show"), this.options.tint && "inner" != this.options.zoomType && this.showHideTint("show")), "hide" == o && ("window" == this.options.zoomType && this.showHideWindow("hide"), this.options.tint || this.showHideWindow("hide"), this.options.showLens && this.showHideLens("hide"), this.options.tint && this.showHideTint("hide"))
			},
			setPosition: function(o) {
				if(!this.options.zoomEnabled) return !1;
				this.nzHeight = this.$elem.height(), this.nzWidth = this.$elem.width(), this.nzOffset = this.$elem.offset(), this.options.tint && "inner" != this.options.zoomType && (this.zoomTint.css({
					top: 0
				}), this.zoomTint.css({
					left: 0
				})), this.options.responsive && !this.options.scrollZoom && this.options.showLens && (this.nzHeight < this.options.zoomWindowWidth / this.widthRatio ? lensHeight = this.nzHeight : lensHeight = String(this.options.zoomWindowHeight / this.heightRatio), this.largeWidth < this.options.zoomWindowWidth ? lensWidth = this.nzWidth : lensWidth = this.options.zoomWindowWidth / this.widthRatio, this.widthRatio = this.largeWidth / this.nzWidth, this.heightRatio = this.largeHeight / this.nzHeight, "lens" != this.options.zoomType && (this.nzHeight < this.options.zoomWindowWidth / this.widthRatio ? lensHeight = this.nzHeight : lensHeight = String(this.options.zoomWindowHeight / this.heightRatio), this.nzWidth < this.options.zoomWindowHeight / this.heightRatio ? lensWidth = this.nzWidth : lensWidth = String(this.options.zoomWindowWidth / this.widthRatio), this.zoomLens.css("width", lensWidth), this.zoomLens.css("height", lensHeight), this.options.tint && (this.zoomTintImage.css("width", this.nzWidth), this.zoomTintImage.css("height", this.nzHeight))), "lens" == this.options.zoomType && this.zoomLens.css({
					width: String(this.options.lensSize) + "px",
					height: String(this.options.lensSize) + "px"
				})), this.zoomContainer.css({
					top: this.nzOffset.top
				}), this.zoomContainer.css({
					left: this.nzOffset.left
				}), this.mouseLeft = parseInt(o.pageX - this.nzOffset.left), this.mouseTop = parseInt(o.pageY - this.nzOffset.top), "window" == this.options.zoomType && (this.Etoppos = this.mouseTop < this.zoomLens.height() / 2, this.Eboppos = this.mouseTop > this.nzHeight - this.zoomLens.height() / 2 - 2 * this.options.lensBorderSize, this.Eloppos = this.mouseLeft < 0 + this.zoomLens.width() / 2, this.Eroppos = this.mouseLeft > this.nzWidth - this.zoomLens.width() / 2 - 2 * this.options.lensBorderSize), "inner" == this.options.zoomType && (this.Etoppos = this.mouseTop < this.nzHeight / 2 / this.heightRatio, this.Eboppos = this.mouseTop > this.nzHeight - this.nzHeight / 2 / this.heightRatio, this.Eloppos = this.mouseLeft < 0 + this.nzWidth / 2 / this.widthRatio, this.Eroppos = this.mouseLeft > this.nzWidth - this.nzWidth / 2 / this.widthRatio - 2 * this.options.lensBorderSize), this.mouseLeft < 0 || this.mouseTop < 0 || this.mouseLeft > this.nzWidth || this.mouseTop > this.nzHeight ? this.setElements("hide") : (this.options.showLens && (this.lensLeftPos = String(Math.floor(this.mouseLeft - this.zoomLens.width() / 2)), this.lensTopPos = String(Math.floor(this.mouseTop - this.zoomLens.height() / 2))), this.Etoppos && (this.lensTopPos = 0), this.Eloppos && (this.windowLeftPos = 0, this.lensLeftPos = 0, this.tintpos = 0), "window" == this.options.zoomType && (this.Eboppos && (this.lensTopPos = Math.max(this.nzHeight - this.zoomLens.height() - 2 * this.options.lensBorderSize, 0)), this.Eroppos && (this.lensLeftPos = this.nzWidth - this.zoomLens.width() - 2 * this.options.lensBorderSize)), "inner" == this.options.zoomType && (this.Eboppos && (this.lensTopPos = Math.max(this.nzHeight - 2 * this.options.lensBorderSize, 0)), this.Eroppos && (this.lensLeftPos = this.nzWidth - this.nzWidth - 2 * this.options.lensBorderSize)), "lens" == this.options.zoomType && (this.windowLeftPos = String(-1 * ((o.pageX - this.nzOffset.left) * this.widthRatio - this.zoomLens.width() / 2)), this.windowTopPos = String(-1 * ((o.pageY - this.nzOffset.top) * this.heightRatio - this.zoomLens.height() / 2)), this.zoomLens.css({
					backgroundPosition: this.windowLeftPos + "px " + this.windowTopPos + "px"
				}), this.changeBgSize && (this.nzHeight > this.nzWidth ? ("lens" == this.options.zoomType && this.zoomLens.css({
					"background-size": this.largeWidth / this.newvalueheight + "px " + this.largeHeight / this.newvalueheight + "px"
				}), this.zoomWindow.css({
					"background-size": this.largeWidth / this.newvalueheight + "px " + this.largeHeight / this.newvalueheight + "px"
				})) : ("lens" == this.options.zoomType && this.zoomLens.css({
					"background-size": this.largeWidth / this.newvaluewidth + "px " + this.largeHeight / this.newvaluewidth + "px"
				}), this.zoomWindow.css({
					"background-size": this.largeWidth / this.newvaluewidth + "px " + this.largeHeight / this.newvaluewidth + "px"
				})), this.changeBgSize = !1), this.setWindowPostition(o)), this.options.tint && "inner" != this.options.zoomType && this.setTintPosition(o), "window" == this.options.zoomType && this.setWindowPostition(o), "inner" == this.options.zoomType && this.setWindowPostition(o), this.options.showLens && (this.fullwidth && "lens" != this.options.zoomType && (this.lensLeftPos = 0), this.zoomLens.css({
					left: this.lensLeftPos + "px",
					top: this.lensTopPos + "px"
				})))
			},
			showHideWindow: function(o) {
				var i = this;
				"show" == o && (i.isWindowActive || (i.options.zoomWindowFadeIn ? i.zoomWindow.stop(!0, !0, !1).fadeIn(i.options.zoomWindowFadeIn) : i.zoomWindow.show(), i.isWindowActive = !0)), "hide" == o && i.isWindowActive && (i.options.zoomWindowFadeOut ? i.zoomWindow.stop(!0, !0).fadeOut(i.options.zoomWindowFadeOut, function() {
					i.loop && (clearInterval(i.loop), i.loop = !1)
				}) : i.zoomWindow.hide(), i.isWindowActive = !1)
			},
			showHideLens: function(o) {
				"show" == o && (this.isLensActive || (this.options.lensFadeIn ? this.zoomLens.stop(!0, !0, !1).fadeIn(this.options.lensFadeIn) : this.zoomLens.show(), this.isLensActive = !0)), "hide" == o && this.isLensActive && (this.options.lensFadeOut ? this.zoomLens.stop(!0, !0).fadeOut(this.options.lensFadeOut) : this.zoomLens.hide(), this.isLensActive = !1)
			},
			showHideTint: function(o) {
				"show" == o && (this.isTintActive || (this.options.zoomTintFadeIn ? this.zoomTint.css({
					opacity: this.options.tintOpacity
				}).animate().stop(!0, !0).fadeIn("slow") : (this.zoomTint.css({
					opacity: this.options.tintOpacity
				}).animate(), this.zoomTint.show()), this.isTintActive = !0)), "hide" == o && this.isTintActive && (this.options.zoomTintFadeOut ? this.zoomTint.stop(!0, !0).fadeOut(this.options.zoomTintFadeOut) : this.zoomTint.hide(), this.isTintActive = !1)
			},
			setLensPostition: function(o) {},
			setWindowPostition: function(i) {
				var t = this;
				if(isNaN(t.options.zoomWindowPosition)) t.externalContainer = o("#" + t.options.zoomWindowPosition), t.externalContainerWidth = t.externalContainer.width(), t.externalContainerHeight = t.externalContainer.height(), t.externalContainerOffset = t.externalContainer.offset(), t.windowOffsetTop = t.externalContainerOffset.top, t.windowOffsetLeft = t.externalContainerOffset.left;
				else switch(t.options.zoomWindowPosition) {
					case 1:
						t.windowOffsetTop = t.options.zoomWindowOffety, t.windowOffsetLeft = +t.nzWidth;
						break;
					case 2:
						t.options.zoomWindowHeight > t.nzHeight && (t.windowOffsetTop = -1 * (t.options.zoomWindowHeight / 2 - t.nzHeight / 2), t.windowOffsetLeft = t.nzWidth);
						break;
					case 3:
						t.windowOffsetTop = t.nzHeight - t.zoomWindow.height() - 2 * t.options.borderSize, t.windowOffsetLeft = t.nzWidth;
						break;
					case 4:
						t.windowOffsetTop = t.nzHeight, t.windowOffsetLeft = t.nzWidth;
						break;
					case 5:
						t.windowOffsetTop = t.nzHeight, t.windowOffsetLeft = t.nzWidth - t.zoomWindow.width() - 2 * t.options.borderSize;
						break;
					case 6:
						t.options.zoomWindowHeight > t.nzHeight && (t.windowOffsetTop = t.nzHeight, t.windowOffsetLeft = -1 * (t.options.zoomWindowWidth / 2 - t.nzWidth / 2 + 2 * t.options.borderSize));
						break;
					case 7:
						t.windowOffsetTop = t.nzHeight, t.windowOffsetLeft = 0;
						break;
					case 8:
						t.windowOffsetTop = t.nzHeight, t.windowOffsetLeft = -1 * (t.zoomWindow.width() + 2 * t.options.borderSize);
						break;
					case 9:
						t.windowOffsetTop = t.nzHeight - t.zoomWindow.height() - 2 * t.options.borderSize, t.windowOffsetLeft = -1 * (t.zoomWindow.width() + 2 * t.options.borderSize);
						break;
					case 10:
						t.options.zoomWindowHeight > t.nzHeight && (t.windowOffsetTop = -1 * (t.options.zoomWindowHeight / 2 - t.nzHeight / 2), t.windowOffsetLeft = -1 * (t.zoomWindow.width() + 2 * t.options.borderSize));
						break;
					case 11:
						t.windowOffsetTop = t.options.zoomWindowOffety, t.windowOffsetLeft = -1 * (t.zoomWindow.width() + 2 * t.options.borderSize);
						break;
					case 12:
						t.windowOffsetTop = -1 * (t.zoomWindow.height() + 2 * t.options.borderSize), t.windowOffsetLeft = -1 * (t.zoomWindow.width() + 2 * t.options.borderSize);
						break;
					case 13:
						t.windowOffsetTop = -1 * (t.zoomWindow.height() + 2 * t.options.borderSize), t.windowOffsetLeft = 0;
						break;
					case 14:
						t.options.zoomWindowHeight > t.nzHeight && (t.windowOffsetTop = -1 * (t.zoomWindow.height() + 2 * t.options.borderSize), t.windowOffsetLeft = -1 * (t.options.zoomWindowWidth / 2 - t.nzWidth / 2 + 2 * t.options.borderSize));
						break;
					case 15:
						t.windowOffsetTop = -1 * (t.zoomWindow.height() + 2 * t.options.borderSize), t.windowOffsetLeft = t.nzWidth - t.zoomWindow.width() - 2 * t.options.borderSize;
						break;
					case 16:
						t.windowOffsetTop = -1 * (t.zoomWindow.height() + 2 * t.options.borderSize), t.windowOffsetLeft = t.nzWidth;
						break;
					default:
						t.windowOffsetTop = t.options.zoomWindowOffety, t.windowOffsetLeft = t.nzWidth
				}
				t.isWindowSet = !0, t.windowOffsetTop = t.windowOffsetTop + t.options.zoomWindowOffety, t.windowOffsetLeft = t.windowOffsetLeft + t.options.zoomWindowOffetx, t.zoomWindow.css({
					top: t.windowOffsetTop
				}), t.zoomWindow.css({
					left: t.windowOffsetLeft
				}), "inner" == t.options.zoomType && (t.zoomWindow.css({
					top: 0
				}), t.zoomWindow.css({
					left: 0
				})), t.windowLeftPos = String(-1 * ((i.pageX - t.nzOffset.left) * t.widthRatio - t.zoomWindow.width() / 2)), t.windowTopPos = String(-1 * ((i.pageY - t.nzOffset.top) * t.heightRatio - t.zoomWindow.height() / 2)), t.Etoppos && (t.windowTopPos = 0), t.Eloppos && (t.windowLeftPos = 0), t.Eboppos && (t.windowTopPos = -1 * (t.largeHeight / t.currentZoomLevel - t.zoomWindow.height())), t.Eroppos && (t.windowLeftPos = -1 * (t.largeWidth / t.currentZoomLevel - t.zoomWindow.width())), t.fullheight && (t.windowTopPos = 0), t.fullwidth && (t.windowLeftPos = 0), "window" != t.options.zoomType && "inner" != t.options.zoomType || (1 == t.zoomLock && (t.widthRatio <= 1 && (t.windowLeftPos = 0), t.heightRatio <= 1 && (t.windowTopPos = 0)), "window" == t.options.zoomType && (t.largeHeight < t.options.zoomWindowHeight && (t.windowTopPos = 0), t.largeWidth < t.options.zoomWindowWidth && (t.windowLeftPos = 0)), t.options.easing ? (t.xp || (t.xp = 0), t.yp || (t.yp = 0), t.loop || (t.loop = setInterval(function() {
					t.xp += (t.windowLeftPos - t.xp) / t.options.easingAmount, t.yp += (t.windowTopPos - t.yp) / t.options.easingAmount, t.scrollingLock ? (clearInterval(t.loop), t.xp = t.windowLeftPos, t.yp = t.windowTopPos, t.xp = -1 * ((i.pageX - t.nzOffset.left) * t.widthRatio - t.zoomWindow.width() / 2), t.yp = -1 * ((i.pageY - t.nzOffset.top) * t.heightRatio - t.zoomWindow.height() / 2), t.changeBgSize && (t.nzHeight > t.nzWidth ? ("lens" == t.options.zoomType && t.zoomLens.css({
						"background-size": t.largeWidth / t.newvalueheight + "px " + t.largeHeight / t.newvalueheight + "px"
					}), t.zoomWindow.css({
						"background-size": t.largeWidth / t.newvalueheight + "px " + t.largeHeight / t.newvalueheight + "px"
					})) : ("lens" != t.options.zoomType && t.zoomLens.css({
						"background-size": t.largeWidth / t.newvaluewidth + "px " + t.largeHeight / t.newvalueheight + "px"
					}), t.zoomWindow.css({
						"background-size": t.largeWidth / t.newvaluewidth + "px " + t.largeHeight / t.newvaluewidth + "px"
					})), t.changeBgSize = !1), t.zoomWindow.css({
						backgroundPosition: t.windowLeftPos + "px " + t.windowTopPos + "px"
					}), t.scrollingLock = !1, t.loop = !1) : Math.round(Math.abs(t.xp - t.windowLeftPos) + Math.abs(t.yp - t.windowTopPos)) < 1 ? (clearInterval(t.loop), t.zoomWindow.css({
						backgroundPosition: t.windowLeftPos + "px " + t.windowTopPos + "px"
					}), t.loop = !1) : (t.changeBgSize && (t.nzHeight > t.nzWidth ? ("lens" == t.options.zoomType && t.zoomLens.css({
						"background-size": t.largeWidth / t.newvalueheight + "px " + t.largeHeight / t.newvalueheight + "px"
					}), t.zoomWindow.css({
						"background-size": t.largeWidth / t.newvalueheight + "px " + t.largeHeight / t.newvalueheight + "px"
					})) : ("lens" != t.options.zoomType && t.zoomLens.css({
						"background-size": t.largeWidth / t.newvaluewidth + "px " + t.largeHeight / t.newvaluewidth + "px"
					}), t.zoomWindow.css({
						"background-size": t.largeWidth / t.newvaluewidth + "px " + t.largeHeight / t.newvaluewidth + "px"
					})), t.changeBgSize = !1), t.zoomWindow.css({
						backgroundPosition: t.xp + "px " + t.yp + "px"
					}))
				}, 16))) : (t.changeBgSize && (t.nzHeight > t.nzWidth ? ("lens" == t.options.zoomType && t.zoomLens.css({
					"background-size": t.largeWidth / t.newvalueheight + "px " + t.largeHeight / t.newvalueheight + "px"
				}), t.zoomWindow.css({
					"background-size": t.largeWidth / t.newvalueheight + "px " + t.largeHeight / t.newvalueheight + "px"
				})) : ("lens" == t.options.zoomType && t.zoomLens.css({
					"background-size": t.largeWidth / t.newvaluewidth + "px " + t.largeHeight / t.newvaluewidth + "px"
				}), t.largeHeight / t.newvaluewidth < t.options.zoomWindowHeight ? t.zoomWindow.css({
					"background-size": t.largeWidth / t.newvaluewidth + "px " + t.largeHeight / t.newvaluewidth + "px"
				}) : t.zoomWindow.css({
					"background-size": t.largeWidth / t.newvalueheight + "px " + t.largeHeight / t.newvalueheight + "px"
				})), t.changeBgSize = !1), t.zoomWindow.css({
					backgroundPosition: t.windowLeftPos + "px " + t.windowTopPos + "px"
				})))
			},
			setTintPosition: function(o) {
				this.nzOffset = this.$elem.offset(), this.tintpos = String(-1 * (o.pageX - this.nzOffset.left - this.zoomLens.width() / 2)), this.tintposy = String(-1 * (o.pageY - this.nzOffset.top - this.zoomLens.height() / 2)), this.Etoppos && (this.tintposy = 0), this.Eloppos && (this.tintpos = 0), this.Eboppos && (this.tintposy = -1 * (this.nzHeight - this.zoomLens.height() - 2 * this.options.lensBorderSize)), this.Eroppos && (this.tintpos = -1 * (this.nzWidth - this.zoomLens.width() - 2 * this.options.lensBorderSize)), this.options.tint && (this.fullheight && (this.tintposy = 0), this.fullwidth && (this.tintpos = 0), this.zoomTintImage.css({
					left: this.tintpos + "px"
				}), this.zoomTintImage.css({
					top: this.tintposy + "px"
				}))
			},
			swaptheimage: function(i, t) {
				var e = this,
					n = new Image;
				e.options.loadingIcon && (e.spinner = o("<div style=\"background: url('" + e.options.loadingIcon + "') no-repeat center;height:" + e.nzHeight + "px;width:" + e.nzWidth + 'px;z-index: 2000;position: absolute; background-position: center center;"></div>'), e.$elem.after(e.spinner)), e.options.onImageSwap(e.$elem), n.onload = function() {
					e.largeWidth = n.width, e.largeHeight = n.height, e.zoomImage = t, e.zoomWindow.css({
						"background-size": e.largeWidth + "px " + e.largeHeight + "px"
					}), e.swapAction(i, t)
				}, n.src = t
			},
			swapAction: function(i, t) {
				var e = this,
					n = new Image;
				if(n.onload = function() {
						e.nzHeight = n.height, e.nzWidth = n.width, e.options.onImageSwapComplete(e.$elem), e.doneCallback()
					}, n.src = i, e.currentZoomLevel = e.options.zoomLevel, e.options.maxZoomLevel = !1, "lens" == e.options.zoomType && e.zoomLens.css({
						backgroundImage: "url('" + t + "')"
					}), "window" == e.options.zoomType && e.zoomWindow.css({
						backgroundImage: "url('" + t + "')"
					}), "inner" == e.options.zoomType && e.zoomWindow.css({
						backgroundImage: "url('" + t + "')"
					}), e.currentImage = t, e.options.imageCrossfade) {
					var s = e.$elem,
						h = s.clone();
					if(e.$elem.attr("src", i), e.$elem.after(h), h.stop(!0).fadeOut(e.options.imageCrossfade, function() {
							o(this).remove()
						}), e.$elem.width("auto").removeAttr("width"), e.$elem.height("auto").removeAttr("height"), s.fadeIn(e.options.imageCrossfade), e.options.tint && "inner" != e.options.zoomType) {
						var a = e.zoomTintImage,
							d = a.clone();
						e.zoomTintImage.attr("src", t), e.zoomTintImage.after(d), d.stop(!0).fadeOut(e.options.imageCrossfade, function() {
							o(this).remove()
						}), a.fadeIn(e.options.imageCrossfade), e.zoomTint.css({
							height: e.$elem.height()
						}), e.zoomTint.css({
							width: e.$elem.width()
						})
					}
					e.zoomContainer.css("height", e.$elem.height()), e.zoomContainer.css("width", e.$elem.width()), "inner" == e.options.zoomType && (e.options.constrainType || (e.zoomWrap.parent().css("height", e.$elem.height()), e.zoomWrap.parent().css("width", e.$elem.width()), e.zoomWindow.css("height", e.$elem.height()), e.zoomWindow.css("width", e.$elem.width()))), e.options.imageCrossfade && (e.zoomWrap.css("height", e.$elem.height()), e.zoomWrap.css("width", e.$elem.width()))
				} else e.$elem.attr("src", i), e.options.tint && (e.zoomTintImage.attr("src", t), e.zoomTintImage.attr("height", e.$elem.height()), e.zoomTintImage.css({
					height: e.$elem.height()
				}), e.zoomTint.css({
					height: e.$elem.height()
				})), e.zoomContainer.css("height", e.$elem.height()), e.zoomContainer.css("width", e.$elem.width()), e.options.imageCrossfade && (e.zoomWrap.css("height", e.$elem.height()), e.zoomWrap.css("width", e.$elem.width()));
				e.options.constrainType && ("height" == e.options.constrainType && (e.zoomContainer.css("height", e.options.constrainSize), e.zoomContainer.css("width", "auto"), e.options.imageCrossfade ? (e.zoomWrap.css("height", e.options.constrainSize), e.zoomWrap.css("width", "auto"), e.constwidth = e.zoomWrap.width()) : (e.$elem.css("height", e.options.constrainSize), e.$elem.css("width", "auto"), e.constwidth = e.$elem.width()), "inner" == e.options.zoomType && (e.zoomWrap.parent().css("height", e.options.constrainSize), e.zoomWrap.parent().css("width", e.constwidth), e.zoomWindow.css("height", e.options.constrainSize), e.zoomWindow.css("width", e.constwidth)), e.options.tint && (e.tintContainer.css("height", e.options.constrainSize), e.tintContainer.css("width", e.constwidth), e.zoomTint.css("height", e.options.constrainSize), e.zoomTint.css("width", e.constwidth), e.zoomTintImage.css("height", e.options.constrainSize), e.zoomTintImage.css("width", e.constwidth))), "width" == e.options.constrainType && (e.zoomContainer.css("height", "auto"), e.zoomContainer.css("width", e.options.constrainSize), e.options.imageCrossfade ? (e.zoomWrap.css("height", "auto"), e.zoomWrap.css("width", e.options.constrainSize), e.constheight = e.zoomWrap.height()) : (e.$elem.css("height", "auto"), e.$elem.css("width", e.options.constrainSize), e.constheight = e.$elem.height()), "inner" == e.options.zoomType && (e.zoomWrap.parent().css("height", e.constheight), e.zoomWrap.parent().css("width", e.options.constrainSize), e.zoomWindow.css("height", e.constheight), e.zoomWindow.css("width", e.options.constrainSize)), e.options.tint && (e.tintContainer.css("height", e.constheight), e.tintContainer.css("width", e.options.constrainSize), e.zoomTint.css("height", e.constheight), e.zoomTint.css("width", e.options.constrainSize), e.zoomTintImage.css("height", e.constheight), e.zoomTintImage.css("width", e.options.constrainSize))))
			},
			doneCallback: function() {
				this.options.loadingIcon && this.spinner.hide(), this.nzOffset = this.$elem.offset(), this.nzWidth = this.$elem.width(), this.nzHeight = this.$elem.height(), this.currentZoomLevel = this.options.zoomLevel, this.widthRatio = this.largeWidth / this.nzWidth, this.heightRatio = this.largeHeight / this.nzHeight, "window" == this.options.zoomType && (this.nzHeight < this.options.zoomWindowWidth / this.widthRatio ? lensHeight = this.nzHeight : lensHeight = String(this.options.zoomWindowHeight / this.heightRatio), this.options.zoomWindowWidth < this.options.zoomWindowWidth ? lensWidth = this.nzWidth : lensWidth = this.options.zoomWindowWidth / this.widthRatio, this.zoomLens && (this.zoomLens.css("width", lensWidth), this.zoomLens.css("height", lensHeight)))
			},
			getCurrentImage: function() {
				return this.zoomImage
			},
			getGalleryList: function() {
				var i = this;
				return i.gallerylist = [], i.options.gallery ? o("#" + i.options.gallery + " a").each(function() {
					var t = "";
					o(this).data("zoom-image") ? t = o(this).data("zoom-image") : o(this).data("image") && (t = o(this).data("image")), t == i.zoomImage ? i.gallerylist.unshift({
						href: "" + t,
						title: o(this).find("img").attr("title")
					}) : i.gallerylist.push({
						href: "" + t,
						title: o(this).find("img").attr("title")
					})
				}) : i.gallerylist.push({
					href: "" + i.zoomImage,
					title: o(this).find("img").attr("title")
				}), i.gallerylist
			},
			changeZoomLevel: function(o) {
				this.scrollingLock = !0, this.newvalue = parseFloat(o).toFixed(2), newvalue = parseFloat(o).toFixed(2), maxheightnewvalue = this.largeHeight / (this.options.zoomWindowHeight / this.nzHeight * this.nzHeight), maxwidthtnewvalue = this.largeWidth / (this.options.zoomWindowWidth / this.nzWidth * this.nzWidth), "inner" != this.options.zoomType && (maxheightnewvalue <= newvalue ? (this.heightRatio = this.largeHeight / maxheightnewvalue / this.nzHeight, this.newvalueheight = maxheightnewvalue, this.fullheight = !0) : (this.heightRatio = this.largeHeight / newvalue / this.nzHeight, this.newvalueheight = newvalue, this.fullheight = !1), maxwidthtnewvalue <= newvalue ? (this.widthRatio = this.largeWidth / maxwidthtnewvalue / this.nzWidth, this.newvaluewidth = maxwidthtnewvalue, this.fullwidth = !0) : (this.widthRatio = this.largeWidth / newvalue / this.nzWidth, this.newvaluewidth = newvalue, this.fullwidth = !1), "lens" == this.options.zoomType && (maxheightnewvalue <= newvalue ? (this.fullwidth = !0, this.newvaluewidth = maxheightnewvalue) : (this.widthRatio = this.largeWidth / newvalue / this.nzWidth, this.newvaluewidth = newvalue, this.fullwidth = !1))), "inner" == this.options.zoomType && (maxheightnewvalue = parseFloat(this.largeHeight / this.nzHeight).toFixed(2), maxwidthtnewvalue = parseFloat(this.largeWidth / this.nzWidth).toFixed(2), newvalue > maxheightnewvalue && (newvalue = maxheightnewvalue), newvalue > maxwidthtnewvalue && (newvalue = maxwidthtnewvalue), maxheightnewvalue <= newvalue ? (this.heightRatio = this.largeHeight / newvalue / this.nzHeight, newvalue > maxheightnewvalue ? this.newvalueheight = maxheightnewvalue : this.newvalueheight = newvalue, this.fullheight = !0) : (this.heightRatio = this.largeHeight / newvalue / this.nzHeight, newvalue > maxheightnewvalue ? this.newvalueheight = maxheightnewvalue : this.newvalueheight = newvalue, this.fullheight = !1), maxwidthtnewvalue <= newvalue ? (this.widthRatio = this.largeWidth / newvalue / this.nzWidth, newvalue > maxwidthtnewvalue ? this.newvaluewidth = maxwidthtnewvalue : this.newvaluewidth = newvalue, this.fullwidth = !0) : (this.widthRatio = this.largeWidth / newvalue / this.nzWidth, this.newvaluewidth = newvalue, this.fullwidth = !1)), scrcontinue = !1, "inner" == this.options.zoomType && (this.nzWidth >= this.nzHeight && (this.newvaluewidth <= maxwidthtnewvalue ? scrcontinue = !0 : (scrcontinue = !1, this.fullheight = !0, this.fullwidth = !0)), this.nzHeight > this.nzWidth && (this.newvaluewidth <= maxwidthtnewvalue ? scrcontinue = !0 : (scrcontinue = !1, this.fullheight = !0, this.fullwidth = !0))), "inner" != this.options.zoomType && (scrcontinue = !0), scrcontinue && (this.zoomLock = 0, this.changeZoom = !0, this.options.zoomWindowHeight / this.heightRatio <= this.nzHeight && (this.currentZoomLevel = this.newvalueheight, "lens" != this.options.zoomType && "inner" != this.options.zoomType && (this.changeBgSize = !0, this.zoomLens.css({
					height: String(this.options.zoomWindowHeight / this.heightRatio) + "px"
				})), "lens" != this.options.zoomType && "inner" != this.options.zoomType || (this.changeBgSize = !0)), this.options.zoomWindowWidth / this.widthRatio <= this.nzWidth && ("inner" != this.options.zoomType && this.newvaluewidth > this.newvalueheight && (this.currentZoomLevel = this.newvaluewidth), "lens" != this.options.zoomType && "inner" != this.options.zoomType && (this.changeBgSize = !0, this.zoomLens.css({
					width: String(this.options.zoomWindowWidth / this.widthRatio) + "px"
				})), "lens" != this.options.zoomType && "inner" != this.options.zoomType || (this.changeBgSize = !0)), "inner" == this.options.zoomType && (this.changeBgSize = !0, this.nzWidth > this.nzHeight && (this.currentZoomLevel = this.newvaluewidth), this.nzHeight > this.nzWidth && (this.currentZoomLevel = this.newvaluewidth))), this.setPosition(this.currentLoc)
			},
			closeAll: function() {
				self.zoomWindow && self.zoomWindow.hide(), self.zoomLens && self.zoomLens.hide(), self.zoomTint && self.zoomTint.hide()
			},
			changeState: function(o) {
				"enable" == o && (this.options.zoomEnabled = !0), "disable" == o && (this.options.zoomEnabled = !1)
			}
		};
		o.fn.elevateZoom = function(i) {
			return this.each(function() {
				var t = Object.create(n);
				t.init(i, this), o.data(this, "elevateZoom", t)
			})
		}, o.fn.elevateZoom.options = {
			zoomActivation: "hover",
			zoomEnabled: !0,
			preloading: 1,
			zoomLevel: 1,
			scrollZoom: !1,
			scrollZoomIncrement: .1,
			minZoomLevel: !1,
			maxZoomLevel: !1,
			easing: !1,
			easingAmount: 12,
			lensSize: 200,
			zoomWindowWidth: 400,
			zoomWindowHeight: 400,
			zoomWindowOffetx: 0,
			zoomWindowOffety: 0,
			zoomWindowPosition: 1,
			zoomWindowBgColour: "#fff",
			lensFadeIn: !1,
			lensFadeOut: !1,
			debug: !1,
			zoomWindowFadeIn: !1,
			zoomWindowFadeOut: !1,
			zoomWindowAlwaysShow: !1,
			zoomTintFadeIn: !1,
			zoomTintFadeOut: !1,
			borderSize: 4,
			showLens: !0,
			borderColour: "#888",
			lensBorderSize: 1,
			lensBorderColour: "#000",
			lensShape: "square",
			zoomType: "window",
			containLensZoom: !1,
			lensColour: "white",
			lensOpacity: .4,
			lenszoom: !1,
			tint: !1,
			tintColour: "#333",
			tintOpacity: .4,
			gallery: !1,
			galleryActiveClass: "zoomGalleryActive",
			imageCrossfade: !1,
			constrainType: !1,
			constrainSize: !1,
			loadingIcon: !1,
			cursor: "default",
			responsive: !0,
			onComplete: o.noop,
			onDestroy: function() {},
			onZoomedImageLoaded: function() {},
			onImageSwap: o.noop,
			onImageSwapComplete: o.noop
		}
	}(jQuery, window, document), $(window).width() > 991 && $(".product-right-slick, .product-slick, .rtl-product-slick, .rtl-product-right-slick").on("afterChange", function(o, i, t, e) {
		$(this).find("img").attr("src");
		var n = $(".image_zoom_cls");
		$(".zoomContainer").remove(), n.removeData("elevateZoom"), n.removeData("zoomImage");
		var s = ".image_zoom_cls-" + t;
		setTimeout(function() {
			$(s).elevateZoom({
				zoomType: "inner",
				cursor: "crosshair"
			})
		}, 200)
	}), $(window).width() > 991 && setTimeout(function() {
		$(".product-right-slick .slick-active img, .product-slick .slick-active img, .rtl-product-slick .slick-active img, .rtl-product-right-slick .slick-active img").elevateZoom({
			zoomType: "inner",
			cursor: "crosshair"
		})
	}, 100);
// Underscore Added from Here
! function(n, r) {
	var t, e;
	"object" == typeof exports && "undefined" != typeof module ? module.exports = r() : "function" == typeof define && define.amd ? define("underscore", r) : (t = n._, e = r(), (n._ = e).noConflict = function() {
		return n._ = t, e
	})
}(this, function() {
	//     Underscore.js 1.10.2
	//     https://underscorejs.org
	//     (c) 2009-2020 Jeremy Ashkenas, DocumentCloud and Investigative Reporters & Editors
	//     Underscore may be freely distributed under the MIT license.
	var n = "object" == typeof self && self.self === self && self || "object" == typeof global && global.global === global && global || Function("return this")() || {},
		e = Array.prototype,
		i = Object.prototype,
		p = "undefined" != typeof Symbol ? Symbol.prototype : null,
		u = e.push,
		f = e.slice,
		s = i.toString,
		o = i.hasOwnProperty,
		r = Array.isArray,
		a = Object.keys,
		t = Object.create,
		c = n.isNaN,
		l = n.isFinite,
		v = function() {};

	function h(n) {
		return n instanceof h ? n : this instanceof h ? void(this._wrapped = n) : new h(n)
	}
	var g = h.VERSION = "1.10.2";

	function y(u, o, n) {
		if(void 0 === o) return u;
		switch(null == n ? 3 : n) {
			case 1:
				return function(n) {
					return u.call(o, n)
				};
			case 3:
				return function(n, r, t) {
					return u.call(o, n, r, t)
				};
			case 4:
				return function(n, r, t, e) {
					return u.call(o, n, r, t, e)
				}
		}
		return function() {
			return u.apply(o, arguments)
		}
	}

	function d(n, r, t) {
		return null == n ? ur : Cn(n) ? y(n, r, t) : Ln(n) && !Kn(n) ? ir(n) : or(n)
	}

	function m(n, r) {
		return d(n, r, 1 / 0)
	}

	function b(n, r, t) {
		return h.iteratee !== m ? h.iteratee(n, r) : d(n, r, t)
	}

	function j(u, o) {
		return o = null == o ? u.length - 1 : +o,
			function() {
				for(var n = Math.max(arguments.length - o, 0), r = Array(n), t = 0; t < n; t++) r[t] = arguments[t + o];
				switch(o) {
					case 0:
						return u.call(this, r);
					case 1:
						return u.call(this, arguments[0], r);
					case 2:
						return u.call(this, arguments[0], arguments[1], r)
				}
				var e = Array(o + 1);
				for(t = 0; t < o; t++) e[t] = arguments[t];
				return e[o] = r, u.apply(this, e)
			}
	}

	function _(n) {
		if(!Ln(n)) return {};
		if(t) return t(n);
		v.prototype = n;
		var r = new v;
		return v.prototype = null, r
	}

	function w(r) {
		return function(n) {
			return null == n ? void 0 : n[r]
		}
	}

	function x(n, r) {
		return null != n && o.call(n, r)
	}

	function S(n, r) {
		for(var t = r.length, e = 0; e < t; e++) {
			if(null == n) return;
			n = n[r[e]]
		}
		return t ? n : void 0
	}
	h.iteratee = m;
	var A = Math.pow(2, 53) - 1,
		O = w("length");

	function M(n) {
		var r = O(n);
		return "number" == typeof r && 0 <= r && r <= A
	}

	function E(n, r, t) {
		var e, u;
		if(r = y(r, t), M(n))
			for(e = 0, u = n.length; e < u; e++) r(n[e], e, n);
		else {
			var o = Sn(n);
			for(e = 0, u = o.length; e < u; e++) r(n[o[e]], o[e], n)
		}
		return n
	}

	function N(n, r, t) {
		r = b(r, t);
		for(var e = !M(n) && Sn(n), u = (e || n).length, o = Array(u), i = 0; i < u; i++) {
			var a = e ? e[i] : i;
			o[i] = r(n[a], a, n)
		}
		return o
	}

	function k(f) {
		return function(n, r, t, e) {
			var u = 3 <= arguments.length;
			return function(n, r, t, e) {
				var u = !M(n) && Sn(n),
					o = (u || n).length,
					i = 0 < f ? 0 : o - 1;
				for(e || (t = n[u ? u[i] : i], i += f); 0 <= i && i < o; i += f) {
					var a = u ? u[i] : i;
					t = r(t, n[a], a, n)
				}
				return t
			}(n, y(r, e, 4), t, u)
		}
	}
	var I = k(1),
		T = k(-1);

	function B(n, r, t) {
		var e = (M(n) ? on : Tn)(n, r, t);
		if(void 0 !== e && -1 !== e) return n[e]
	}

	function R(n, e, r) {
		var u = [];
		return e = b(e, r), E(n, function(n, r, t) {
			e(n, r, t) && u.push(n)
		}), u
	}

	function F(n, r, t) {
		r = b(r, t);
		for(var e = !M(n) && Sn(n), u = (e || n).length, o = 0; o < u; o++) {
			var i = e ? e[o] : o;
			if(!r(n[i], i, n)) return !1
		}
		return !0
	}

	function q(n, r, t) {
		r = b(r, t);
		for(var e = !M(n) && Sn(n), u = (e || n).length, o = 0; o < u; o++) {
			var i = e ? e[o] : o;
			if(r(n[i], i, n)) return !0
		}
		return !1
	}

	function D(n, r, t, e) {
		return M(n) || (n = On(n)), ("number" != typeof t || e) && (t = 0), 0 <= ln(n, r, t)
	}
	var W = j(function(n, t, e) {
		var u, o;
		return Cn(t) ? o = t : Kn(t) && (u = t.slice(0, -1), t = t[t.length - 1]), N(n, function(n) {
			var r = o;
			if(!r) {
				if(u && u.length && (n = S(n, u)), null == n) return;
				r = n[t]
			}
			return null == r ? r : r.apply(n, e)
		})
	});

	function z(n, r) {
		return N(n, or(r))
	}

	function P(n, e, r) {
		var t, u, o = -1 / 0,
			i = -1 / 0;
		if(null == e || "number" == typeof e && "object" != typeof n[0] && null != n)
			for(var a = 0, f = (n = M(n) ? n : On(n)).length; a < f; a++) null != (t = n[a]) && o < t && (o = t);
		else e = b(e, r), E(n, function(n, r, t) {
			u = e(n, r, t), (i < u || u === -1 / 0 && o === -1 / 0) && (o = n, i = u)
		});
		return o
	}

	function K(n, r, t) {
		if(null == r || t) return M(n) || (n = On(n)), n[ar(n.length - 1)];
		var e = M(n) ? Dn(n) : On(n),
			u = O(e);
		r = Math.max(Math.min(r, u), 0);
		for(var o = u - 1, i = 0; i < r; i++) {
			var a = ar(i, o),
				f = e[i];
			e[i] = e[a], e[a] = f
		}
		return e.slice(0, r)
	}

	function L(i, r) {
		return function(e, u, n) {
			var o = r ? [
				[],
				[]
			] : {};
			return u = b(u, n), E(e, function(n, r) {
				var t = u(n, r, e);
				i(o, n, t)
			}), o
		}
	}
	var V = L(function(n, r, t) {
			x(n, t) ? n[t].push(r) : n[t] = [r]
		}),
		C = L(function(n, r, t) {
			n[t] = r
		}),
		J = L(function(n, r, t) {
			x(n, t) ? n[t]++ : n[t] = 1
		}),
		U = /[^\ud800-\udfff]|[\ud800-\udbff][\udc00-\udfff]|[\ud800-\udfff]/g;
	var $ = L(function(n, r, t) {
		n[t ? 0 : 1].push(r)
	}, !0);

	function G(n, r, t) {
		return null == n || n.length < 1 ? null == r ? void 0 : [] : null == r || t ? n[0] : H(n, n.length - r)
	}

	function H(n, r, t) {
		return f.call(n, 0, Math.max(0, n.length - (null == r || t ? 1 : r)))
	}

	function Q(n, r, t) {
		return f.call(n, null == r || t ? 1 : r)
	}

	function X(n, r, t, e) {
		for(var u = (e = e || []).length, o = 0, i = O(n); o < i; o++) {
			var a = n[o];
			if(M(a) && (Kn(a) || Vn(a)))
				if(r)
					for(var f = 0, c = a.length; f < c;) e[u++] = a[f++];
				else X(a, r, t, e), u = e.length;
			else t || (e[u++] = a)
		}
		return e
	}
	var Y = j(function(n, r) {
		return rn(n, r)
	});

	function Z(n, r, t, e) {
		er(r) || (e = t, t = r, r = !1), null != t && (t = b(t, e));
		for(var u = [], o = [], i = 0, a = O(n); i < a; i++) {
			var f = n[i],
				c = t ? t(f, i, n) : f;
			r && !t ? (i && o === c || u.push(f), o = c) : t ? D(o, c) || (o.push(c), u.push(f)) : D(u, f) || u.push(f)
		}
		return u
	}
	var nn = j(function(n) {
		return Z(X(n, !0, !0))
	});
	var rn = j(function(n, r) {
		return r = X(r, !0, !0), R(n, function(n) {
			return !D(r, n)
		})
	});

	function tn(n) {
		for(var r = n && P(n, O).length || 0, t = Array(r), e = 0; e < r; e++) t[e] = z(n, e);
		return t
	}
	var en = j(tn);

	function un(o) {
		return function(n, r, t) {
			r = b(r, t);
			for(var e = O(n), u = 0 < o ? 0 : e - 1; 0 <= u && u < e; u += o)
				if(r(n[u], u, n)) return u;
			return -1
		}
	}
	var on = un(1),
		an = un(-1);

	function fn(n, r, t, e) {
		for(var u = (t = b(t, e, 1))(r), o = 0, i = O(n); o < i;) {
			var a = Math.floor((o + i) / 2);
			t(n[a]) < u ? o = a + 1 : i = a
		}
		return o
	}

	function cn(o, i, a) {
		return function(n, r, t) {
			var e = 0,
				u = O(n);
			if("number" == typeof t) 0 < o ? e = 0 <= t ? t : Math.max(t + u, e) : u = 0 <= t ? Math.min(t + 1, u) : t + u + 1;
			else if(a && t && u) return n[t = a(n, r)] === r ? t : -1;
			if(r != r) return 0 <= (t = i(f.call(n, e, u), tr)) ? t + e : -1;
			for(t = 0 < o ? e : u - 1; 0 <= t && t < u; t += o)
				if(n[t] === r) return t;
			return -1
		}
	}
	var ln = cn(1, on, fn),
		pn = cn(-1, an);

	function sn(n, r, t, e, u) {
		if(!(e instanceof r)) return n.apply(t, u);
		var o = _(n.prototype),
			i = n.apply(o, u);
		return Ln(i) ? i : o
	}
	var vn = j(function(r, t, e) {
			if(!Cn(r)) throw new TypeError("Bind must be called on a function");
			var u = j(function(n) {
				return sn(r, u, t, this, e.concat(n))
			});
			return u
		}),
		hn = j(function(u, o) {
			var i = hn.placeholder,
				a = function() {
					for(var n = 0, r = o.length, t = Array(r), e = 0; e < r; e++) t[e] = o[e] === i ? arguments[n++] : o[e];
					for(; n < arguments.length;) t.push(arguments[n++]);
					return sn(u, a, this, this, t)
				};
			return a
		});
	hn.placeholder = h;
	var gn = j(function(n, r) {
		var t = (r = X(r, !1, !1)).length;
		if(t < 1) throw new Error("bindAll must be passed function names");
		for(; t--;) {
			var e = r[t];
			n[e] = vn(n[e], n)
		}
	});
	var yn = j(function(n, r, t) {
			return setTimeout(function() {
				return n.apply(null, t)
			}, r)
		}),
		dn = hn(yn, h, 1);

	function mn(n) {
		return function() {
			return !n.apply(this, arguments)
		}
	}

	function bn(n, r) {
		var t;
		return function() {
			return 0 < --n && (t = r.apply(this, arguments)), n <= 1 && (r = null), t
		}
	}
	var jn = hn(bn, 2),
		_n = !{
			toString: null
		}.propertyIsEnumerable("toString"),
		wn = ["valueOf", "isPrototypeOf", "toString", "propertyIsEnumerable", "hasOwnProperty", "toLocaleString"];

	function xn(n, r) {
		var t = wn.length,
			e = n.constructor,
			u = Cn(e) && e.prototype || i,
			o = "constructor";
		for(x(n, o) && !D(r, o) && r.push(o); t--;)(o = wn[t]) in n && n[o] !== u[o] && !D(r, o) && r.push(o)
	}

	function Sn(n) {
		if(!Ln(n)) return [];
		if(a) return a(n);
		var r = [];
		for(var t in n) x(n, t) && r.push(t);
		return _n && xn(n, r), r
	}

	function An(n) {
		if(!Ln(n)) return [];
		var r = [];
		for(var t in n) r.push(t);
		return _n && xn(n, r), r
	}

	function On(n) {
		for(var r = Sn(n), t = r.length, e = Array(t), u = 0; u < t; u++) e[u] = n[r[u]];
		return e
	}

	function Mn(n) {
		for(var r = {}, t = Sn(n), e = 0, u = t.length; e < u; e++) r[n[t[e]]] = t[e];
		return r
	}

	function En(n) {
		var r = [];
		for(var t in n) Cn(n[t]) && r.push(t);
		return r.sort()
	}

	function Nn(f, c) {
		return function(n) {
			var r = arguments.length;
			if(c && (n = Object(n)), r < 2 || null == n) return n;
			for(var t = 1; t < r; t++)
				for(var e = arguments[t], u = f(e), o = u.length, i = 0; i < o; i++) {
					var a = u[i];
					c && void 0 !== n[a] || (n[a] = e[a])
				}
			return n
		}
	}
	var kn = Nn(An),
		In = Nn(Sn);

	function Tn(n, r, t) {
		r = b(r, t);
		for(var e, u = Sn(n), o = 0, i = u.length; o < i; o++)
			if(r(n[e = u[o]], e, n)) return e
	}

	function Bn(n, r, t) {
		return r in t
	}
	var Rn = j(function(n, r) {
			var t = {},
				e = r[0];
			if(null == n) return t;
			Cn(e) ? (1 < r.length && (e = y(e, r[1])), r = An(n)) : (e = Bn, r = X(r, !1, !1), n = Object(n));
			for(var u = 0, o = r.length; u < o; u++) {
				var i = r[u],
					a = n[i];
				e(a, i, n) && (t[i] = a)
			}
			return t
		}),
		Fn = j(function(n, t) {
			var r, e = t[0];
			return Cn(e) ? (e = mn(e), 1 < t.length && (r = t[1])) : (t = N(X(t, !1, !1), String), e = function(n, r) {
				return !D(t, r)
			}), Rn(n, e, r)
		}),
		qn = Nn(An, !0);

	function Dn(n) {
		return Ln(n) ? Kn(n) ? n.slice() : kn({}, n) : n
	}

	function Wn(n, r) {
		var t = Sn(r),
			e = t.length;
		if(null == n) return !e;
		for(var u = Object(n), o = 0; o < e; o++) {
			var i = t[o];
			if(r[i] !== u[i] || !(i in u)) return !1
		}
		return !0
	}

	function zn(n, r, t, e) {
		if(n === r) return 0 !== n || 1 / n == 1 / r;
		if(null == n || null == r) return !1;
		if(n != n) return r != r;
		var u = typeof n;
		return("function" === u || "object" === u || "object" == typeof r) && function(n, r, t, e) {
			n instanceof h && (n = n._wrapped);
			r instanceof h && (r = r._wrapped);
			var u = s.call(n);
			if(u !== s.call(r)) return !1;
			switch(u) {
				case "[object RegExp]":
				case "[object String]":
					return "" + n == "" + r;
				case "[object Number]":
					return +n != +n ? +r != +r : 0 == +n ? 1 / +n == 1 / r : +n == +r;
				case "[object Date]":
				case "[object Boolean]":
					return +n == +r;
				case "[object Symbol]":
					return p.valueOf.call(n) === p.valueOf.call(r)
			}
			var o = "[object Array]" === u;
			if(!o) {
				if("object" != typeof n || "object" != typeof r) return !1;
				var i = n.constructor,
					a = r.constructor;
				if(i !== a && !(Cn(i) && i instanceof i && Cn(a) && a instanceof a) && "constructor" in n && "constructor" in r) return !1
			}
			e = e || [];
			var f = (t = t || []).length;
			for(; f--;)
				if(t[f] === n) return e[f] === r;
			if(t.push(n), e.push(r), o) {
				if((f = n.length) !== r.length) return !1;
				for(; f--;)
					if(!zn(n[f], r[f], t, e)) return !1
			} else {
				var c, l = Sn(n);
				if(f = l.length, Sn(r).length !== f) return !1;
				for(; f--;)
					if(c = l[f], !x(r, c) || !zn(n[c], r[c], t, e)) return !1
			}
			return t.pop(), e.pop(), !0
		}(n, r, t, e)
	}

	function Pn(r) {
		return function(n) {
			return s.call(n) === "[object " + r + "]"
		}
	}
	var Kn = r || Pn("Array");

	function Ln(n) {
		var r = typeof n;
		return "function" === r || "object" === r && !!n
	}
	var Vn = Pn("Arguments"),
		Cn = Pn("Function"),
		Jn = Pn("String"),
		Un = Pn("Number"),
		$n = Pn("Date"),
		Gn = Pn("RegExp"),
		Hn = Pn("Error"),
		Qn = Pn("Symbol"),
		Xn = Pn("Map"),
		Yn = Pn("WeakMap"),
		Zn = Pn("Set"),
		nr = Pn("WeakSet");
	! function() {
		Vn(arguments) || (Vn = function(n) {
			return x(n, "callee")
		})
	}();
	var rr = n.document && n.document.childNodes;

	function tr(n) {
		return Un(n) && c(n)
	}

	function er(n) {
		return !0 === n || !1 === n || "[object Boolean]" === s.call(n)
	}

	function ur(n) {
		return n
	}

	function or(r) {
		return Kn(r) ? function(n) {
			return S(n, r)
		} : w(r)
	}

	function ir(r) {
		return r = In({}, r),
			function(n) {
				return Wn(n, r)
			}
	}

	function ar(n, r) {
		return null == r && (r = n, n = 0), n + Math.floor(Math.random() * (r - n + 1))
	}
	"function" != typeof /./ && "object" != typeof Int8Array && "function" != typeof rr && (Cn = function(n) {
		return "function" == typeof n || !1
	});
	var fr = Date.now || function() {
			return(new Date).getTime()
		},
		cr = {
			"&": "&amp;",
			"<": "&lt;",
			">": "&gt;",
			'"': "&quot;",
			"'": "&#x27;",
			"`": "&#x60;"
		},
		lr = Mn(cr);

	function pr(r) {
		var t = function(n) {
				return r[n]
			},
			n = "(?:" + Sn(r).join("|") + ")",
			e = RegExp(n),
			u = RegExp(n, "g");
		return function(n) {
			return n = null == n ? "" : "" + n, e.test(n) ? n.replace(u, t) : n
		}
	}
	var sr = pr(cr),
		vr = pr(lr);
	var hr = 0;
	var gr = h.templateSettings = {
			evaluate: /<%([\s\S]+?)%>/g,
			interpolate: /<%=([\s\S]+?)%>/g,
			escape: /<%-([\s\S]+?)%>/g
		},
		yr = /(.)^/,
		dr = {
			"'": "'",
			"\\": "\\",
			"\r": "r",
			"\n": "n",
			"\u2028": "u2028",
			"\u2029": "u2029"
		},
		mr = /\\|'|\r|\n|\u2028|\u2029/g,
		br = function(n) {
			return "\\" + dr[n]
		};

	function jr(n, r) {
		return n._chain ? h(r).chain() : r
	}

	function _r(t) {
		return E(En(t), function(n) {
			var r = h[n] = t[n];
			h.prototype[n] = function() {
				var n = [this._wrapped];
				return u.apply(n, arguments), jr(this, r.apply(h, n))
			}
		}), h
	}
	E(["pop", "push", "reverse", "shift", "sort", "splice", "unshift"], function(r) {
		var t = e[r];
		h.prototype[r] = function() {
			var n = this._wrapped;
			return t.apply(n, arguments), "shift" !== r && "splice" !== r || 0 !== n.length || delete n[0], jr(this, n)
		}
	}), E(["concat", "join", "slice"], function(n) {
		var r = e[n];
		h.prototype[n] = function() {
			return jr(this, r.apply(this._wrapped, arguments))
		}
	}), h.prototype.valueOf = h.prototype.toJSON = h.prototype.value = function() {
		return this._wrapped
	}, h.prototype.toString = function() {
		return String(this._wrapped)
	};
	var wr = _r({
		default: h,
		VERSION: g,
		iteratee: m,
		restArguments: j,
		each: E,
		forEach: E,
		map: N,
		collect: N,
		reduce: I,
		foldl: I,
		inject: I,
		reduceRight: T,
		foldr: T,
		find: B,
		detect: B,
		filter: R,
		select: R,
		reject: function(n, r, t) {
			return R(n, mn(b(r)), t)
		},
		every: F,
		all: F,
		some: q,
		any: q,
		contains: D,
		includes: D,
		include: D,
		invoke: W,
		pluck: z,
		where: function(n, r) {
			return R(n, ir(r))
		},
		findWhere: function(n, r) {
			return B(n, ir(r))
		},
		max: P,
		min: function(n, e, r) {
			var t, u, o = 1 / 0,
				i = 1 / 0;
			if(null == e || "number" == typeof e && "object" != typeof n[0] && null != n)
				for(var a = 0, f = (n = M(n) ? n : On(n)).length; a < f; a++) null != (t = n[a]) && t < o && (o = t);
			else e = b(e, r), E(n, function(n, r, t) {
				((u = e(n, r, t)) < i || u === 1 / 0 && o === 1 / 0) && (o = n, i = u)
			});
			return o
		},
		shuffle: function(n) {
			return K(n, 1 / 0)
		},
		sample: K,
		sortBy: function(n, e, r) {
			var u = 0;
			return e = b(e, r), z(N(n, function(n, r, t) {
				return {
					value: n,
					index: u++,
					criteria: e(n, r, t)
				}
			}).sort(function(n, r) {
				var t = n.criteria,
					e = r.criteria;
				if(t !== e) {
					if(e < t || void 0 === t) return 1;
					if(t < e || void 0 === e) return -1
				}
				return n.index - r.index
			}), "value")
		},
		groupBy: V,
		indexBy: C,
		countBy: J,
		toArray: function(n) {
			return n ? Kn(n) ? f.call(n) : Jn(n) ? n.match(U) : M(n) ? N(n, ur) : On(n) : []
		},
		size: function(n) {
			return null == n ? 0 : M(n) ? n.length : Sn(n).length
		},
		partition: $,
		first: G,
		head: G,
		take: G,
		initial: H,
		last: function(n, r, t) {
			return null == n || n.length < 1 ? null == r ? void 0 : [] : null == r || t ? n[n.length - 1] : Q(n, Math.max(0, n.length - r))
		},
		rest: Q,
		tail: Q,
		drop: Q,
		compact: function(n) {
			return R(n, Boolean)
		},
		flatten: function(n, r) {
			return X(n, r, !1)
		},
		without: Y,
		uniq: Z,
		unique: Z,
		union: nn,
		intersection: function(n) {
			for(var r = [], t = arguments.length, e = 0, u = O(n); e < u; e++) {
				var o = n[e];
				if(!D(r, o)) {
					var i;
					for(i = 1; i < t && D(arguments[i], o); i++);
					i === t && r.push(o)
				}
			}
			return r
		},
		difference: rn,
		unzip: tn,
		zip: en,
		object: function(n, r) {
			for(var t = {}, e = 0, u = O(n); e < u; e++) r ? t[n[e]] = r[e] : t[n[e][0]] = n[e][1];
			return t
		},
		findIndex: on,
		findLastIndex: an,
		sortedIndex: fn,
		indexOf: ln,
		lastIndexOf: pn,
		range: function(n, r, t) {
			null == r && (r = n || 0, n = 0), t || (t = r < n ? -1 : 1);
			for(var e = Math.max(Math.ceil((r - n) / t), 0), u = Array(e), o = 0; o < e; o++, n += t) u[o] = n;
			return u
		},
		chunk: function(n, r) {
			if(null == r || r < 1) return [];
			for(var t = [], e = 0, u = n.length; e < u;) t.push(f.call(n, e, e += r));
			return t
		},
		bind: vn,
		partial: hn,
		bindAll: gn,
		memoize: function(e, u) {
			var o = function(n) {
				var r = o.cache,
					t = "" + (u ? u.apply(this, arguments) : n);
				return x(r, t) || (r[t] = e.apply(this, arguments)), r[t]
			};
			return o.cache = {}, o
		},
		delay: yn,
		defer: dn,
		throttle: function(t, e, u) {
			var o, i, a, f, c = 0;
			u || (u = {});
			var l = function() {
					c = !1 === u.leading ? 0 : fr(), o = null, f = t.apply(i, a), o || (i = a = null)
				},
				n = function() {
					var n = fr();
					c || !1 !== u.leading || (c = n);
					var r = e - (n - c);
					return i = this, a = arguments, r <= 0 || e < r ? (o && (clearTimeout(o), o = null), c = n, f = t.apply(i, a), o || (i = a = null)) : o || !1 === u.trailing || (o = setTimeout(l, r)), f
				};
			return n.cancel = function() {
				clearTimeout(o), c = 0, o = i = a = null
			}, n
		},
		debounce: function(t, e, u) {
			var o, i, a = function(n, r) {
					o = null, r && (i = t.apply(n, r))
				},
				n = j(function(n) {
					if(o && clearTimeout(o), u) {
						var r = !o;
						o = setTimeout(a, e), r && (i = t.apply(this, n))
					} else o = yn(a, e, this, n);
					return i
				});
			return n.cancel = function() {
				clearTimeout(o), o = null
			}, n
		},
		wrap: function(n, r) {
			return hn(r, n)
		},
		negate: mn,
		compose: function() {
			var t = arguments,
				e = t.length - 1;
			return function() {
				for(var n = e, r = t[e].apply(this, arguments); n--;) r = t[n].call(this, r);
				return r
			}
		},
		after: function(n, r) {
			return function() {
				if(--n < 1) return r.apply(this, arguments)
			}
		},
		before: bn,
		once: jn,
		keys: Sn,
		allKeys: An,
		values: On,
		mapObject: function(n, r, t) {
			r = b(r, t);
			for(var e = Sn(n), u = e.length, o = {}, i = 0; i < u; i++) {
				var a = e[i];
				o[a] = r(n[a], a, n)
			}
			return o
		},
		pairs: function(n) {
			for(var r = Sn(n), t = r.length, e = Array(t), u = 0; u < t; u++) e[u] = [r[u], n[r[u]]];
			return e
		},
		invert: Mn,
		functions: En,
		methods: En,
		extend: kn,
		extendOwn: In,
		assign: In,
		findKey: Tn,
		pick: Rn,
		omit: Fn,
		defaults: qn,
		create: function(n, r) {
			var t = _(n);
			return r && In(t, r), t
		},
		clone: Dn,
		tap: function(n, r) {
			return r(n), n
		},
		isMatch: Wn,
		isEqual: function(n, r) {
			return zn(n, r)
		},
		isEmpty: function(n) {
			return null == n || (M(n) && (Kn(n) || Jn(n) || Vn(n)) ? 0 === n.length : 0 === Sn(n).length)
		},
		isElement: function(n) {
			return !(!n || 1 !== n.nodeType)
		},
		isArray: Kn,
		isObject: Ln,
		isArguments: Vn,
		isFunction: Cn,
		isString: Jn,
		isNumber: Un,
		isDate: $n,
		isRegExp: Gn,
		isError: Hn,
		isSymbol: Qn,
		isMap: Xn,
		isWeakMap: Yn,
		isSet: Zn,
		isWeakSet: nr,
		isFinite: function(n) {
			return !Qn(n) && l(n) && !c(parseFloat(n))
		},
		isNaN: tr,
		isBoolean: er,
		isNull: function(n) {
			return null === n
		},
		isUndefined: function(n) {
			return void 0 === n
		},
		has: function(n, r) {
			if(!Kn(r)) return x(n, r);
			for(var t = r.length, e = 0; e < t; e++) {
				var u = r[e];
				if(null == n || !o.call(n, u)) return !1;
				n = n[u]
			}
			return !!t
		},
		identity: ur,
		constant: function(n) {
			return function() {
				return n
			}
		},
		noop: function() {},
		property: or,
		propertyOf: function(r) {
			return null == r ? function() {} : function(n) {
				return Kn(n) ? S(r, n) : r[n]
			}
		},
		matcher: ir,
		matches: ir,
		times: function(n, r, t) {
			var e = Array(Math.max(0, n));
			r = y(r, t, 1);
			for(var u = 0; u < n; u++) e[u] = r(u);
			return e
		},
		random: ar,
		now: fr,
		escape: sr,
		unescape: vr,
		result: function(n, r, t) {
			Kn(r) || (r = [r]);
			var e = r.length;
			if(!e) return Cn(t) ? t.call(n) : t;
			for(var u = 0; u < e; u++) {
				var o = null == n ? void 0 : n[r[u]];
				void 0 === o && (o = t, u = e), n = Cn(o) ? o.call(n) : o
			}
			return n
		},
		uniqueId: function(n) {
			var r = ++hr + "";
			return n ? n + r : r
		},
		templateSettings: gr,
		template: function(o, n, r) {
			!n && r && (n = r), n = qn({}, n, h.templateSettings);
			var t, e = RegExp([(n.escape || yr).source, (n.interpolate || yr).source, (n.evaluate || yr).source].join("|") + "|$", "g"),
				i = 0,
				a = "__p+='";
			o.replace(e, function(n, r, t, e, u) {
				return a += o.slice(i, u).replace(mr, br), i = u + n.length, r ? a += "'+\n((__t=(" + r + "))==null?'':_.escape(__t))+\n'" : t ? a += "'+\n((__t=(" + t + "))==null?'':__t)+\n'" : e && (a += "';\n" + e + "\n__p+='"), n
			}), a += "';\n", n.variable || (a = "with(obj||{}){\n" + a + "}\n"), a = "var __t,__p='',__j=Array.prototype.join," + "print=function(){__p+=__j.call(arguments,'');};\n" + a + "return __p;\n";
			try {
				t = new Function(n.variable || "obj", "_", a)
			} catch(n) {
				throw n.source = a, n
			}
			var u = function(n) {
					return t.call(this, n, h)
				},
				f = n.variable || "obj";
			return u.source = "function(" + f + "){\n" + a + "}", u
		},
		chain: function(n) {
			var r = h(n);
			return r._chain = !0, r
		},
		mixin: _r
	});
	return wr._ = wr
});
// Scripts Js Added From Here
! function(e) {
	"use strict";
	if(e(window).on("load", function() {
			setTimeout(function() {
				e(".loader_skeleton").fadeOut("slow"), e("body").css({
					overflow: ""
				})
			}, 500), e(".loader_skeleton").remove("slow")
		}), e("#preloader").fadeOut("slow", function() {
			e(this).remove()
		}), e(window).on("scroll", function() {
			e(this).scrollTop() > 600 ? e(".tap-top").fadeIn() : e(".tap-top").fadeOut()
		}), e(".tap-top").on("click", function() {
			return e("html, body").animate({
				scrollTop: 0
			}, 600), !1
		}), e(window).on("load", function() {
			e("#ageModal").modal("show")
		}), e(window).width() > "1200" && e("#hover-cls").hover(function() {
			e(".sm").addClass("hover-unset")
		}), e(window).width() > "1200" && e("#sub-menu > li").hover(function() {
			e(this).children().hasClass("has-submenu") && e(this).parents().find("nav").addClass("sidebar-unset")
		}, function() {
			e(this).parents().find("nav").removeClass("sidebar-unset")
		}), e(".bg-top").parent().addClass("b-top"), e(".bg-bottom").parent().addClass("b-bottom"), e(".bg-center").parent().addClass("b-center"), e(".bg_size_content").parent().addClass("b_size_content"), e(".bg-img").parent().addClass("bg-size"), e(".bg-img.blur-up").parent().addClass("blur-up lazyload"), jQuery(".bg-img").each(function() {
			var s = e(this),
				i = s.attr("src");
			s.parent().css({
				"background-image": "url(" + i + ")",
				"background-size": "cover",
				"background-position": "center",
				display: "block"
			}), s.hide()
		}), e(".filter-button").click(function() {
			e(this).addClass("active").siblings(".active").removeClass("active");
			var s = e(this).attr("data-filter");
			"all" == s ? e(".filter").show("1000") : (e(".filter").not("." + s).hide("3000"), e(".filter").filter("." + s).show("3000"))
		}), e("#formButton").click(function() {
			e("#form1").toggle()
		}), e(".heading-right h3").click(function() {
			e(".offer-box").toggleClass("toggle-cls")
		}), e(".toggle-nav").on("click", function() {
			e(".sm-horizontal").css("right", "0px")
		}), e(".mobile-back").on("click", function() {
			e(".sm-horizontal").css("right", "-410px")
		}), jQuery(window).width() < "750" ? (jQuery(".footer-title h4").append('<span class="according-menu"></span>'), jQuery(".footer-title").on("click", function() {
			jQuery(".footer-title").removeClass("active"), jQuery(".footer-contant").slideUp("normal"), 1 == jQuery(this).next().is(":hidden") && (jQuery(this).addClass("active"), jQuery(this).next().slideDown("normal"))
		}), jQuery(".footer-contant").hide()) : jQuery(".footer-contant").show(), e(window).width() < "1183" ? (jQuery(".menu-title h5").append('<span class="according-menu"></span>'), jQuery(".menu-title").on("click", function() {
			jQuery(".menu-title").removeClass("active"), jQuery(".menu-content").slideUp("normal"), 1 == jQuery(this).next().is(":hidden") && (jQuery(this).addClass("active"), jQuery(this).next().slideDown("normal"))
		}), jQuery(".menu-content").hide()) : jQuery(".menu-content").show(), e("button.add-button").click(function() {
			e(this).next().addClass("open"), e(".qty-input").val("1")
		}), e(".quantity-right-plus").on("click", function() {
			var s = e(this).siblings(".qty-input"),
				i = parseInt(s.val());
			isNaN(i) || s.val(i + 1)
		}), e(".quantity-left-minus").on("click", function() {
			var s = e(this).siblings(".qty-input");
			if("1" == e(s).val()) {
				var i = e(this).parents(".cart_qty");
				e(i).removeClass("open")
			}
			var o = parseInt(s.val());
			!isNaN(o) && o > 0 && s.val(o - 1)
		}), e(".collection-wrapper .qty-box .quantity-right-plus").on("click", function() {
			var s = e(".qty-box .input-number"),
				i = parseInt(s.val(), 10);
			isNaN(i) || s.val(i + 1)
		}), e(".collection-wrapper .qty-box .quantity-left-minus").on("click", function() {
			var s = e(".qty-box .input-number"),
				i = parseInt(s.val(), 10);
			!isNaN(i) && i > 1 && s.val(i - 1)
		}), e(window).width() > 767) {
		function s(s) {
			e(window).on("wheel", {
				$slider: s
			}, i)
		}

		function i(e) {
			e.preventDefault();
			var s = e.data.$slider;
			e.originalEvent.deltaY > 0 ? s.slick("slickNext") : s.slick("slickPrev")
		}(o = e(".full-slider")).on("init", function() {
			s(o)
		}).slick({
			dots: !0,
			nav: !1,
			vertical: !0,
			infinite: !1
		})
	} else {
		var o;

		function s(s) {
			e(window).on("wheel", {
				$slider: s
			}, i)
		}

		function i(e) {
			e.preventDefault();
			var s = e.data.$slider;
			e.originalEvent.deltaY > 0 ? s.slick("slickNext") : s.slick("slickPrev")
		}(o = e(".full-slider")).on("init", function() {
			s(o)
		}).slick({
			dots: !0,
			nav: !1,
			vertical: !1,
			infinite: !1
		})
	}
	"rtl" == $("body").attr("dir") ? e(".slide-1").slick({
		rtl: !0,
		autoplay: !0,
		autoplaySpeed: 3e3
	}) : e(".slide-1").slick({
		autoplay: !0,
		autoplaySpeed: 3e3
	}), e(".slide-2").slick({
		infinite: !0,
		slidesToShow: 2,
		slidesToScroll: 2,
		responsive: [{
			breakpoint: 991,
			settings: {
				slidesToShow: 1,
				slidesToScroll: 1
			}
		}]
	}), e(".slide-3").slick({
		infinite: !0,
		speed: 300,
		slidesToShow: 3,
		slidesToScroll: 1,
		autoplay: !0,
		autoplaySpeed: 5e3,
		responsive: [{
			breakpoint: 1200,
			settings: {
				slidesToShow: 2,
				slidesToScroll: 2
			}
		}, {
			breakpoint: 767,
			settings: {
				slidesToShow: 1,
				slidesToScroll: 1
			}
		}]
	}), e(".team-4").slick({
		infinite: !0,
		speed: 300,
		slidesToShow: 4,
		slidesToScroll: 1,
		autoplay: !0,
		autoplaySpeed: 3e3,
		responsive: [{
			breakpoint: 1200,
			settings: {
				slidesToShow: 3,
				slidesToScroll: 3
			}
		}, {
			breakpoint: 991,
			settings: {
				slidesToShow: 2,
				slidesToScroll: 2
			}
		}, {
			breakpoint: 586,
			settings: {
				slidesToShow: 2,
				slidesToScroll: 1
			}
		}]
	}), e(".slide-4").slick({
		infinite: !1,
		speed: 300,
		slidesToShow: 4,
		slidesToScroll: 1,
		autoplay: !0,
		autoplaySpeed: 5e3,
		responsive: [{
			breakpoint: 1200,
			settings: {
				slidesToShow: 3,
				slidesToScroll: 3
			}
		}, {
			breakpoint: 991,
			settings: {
				slidesToShow: 2,
				slidesToScroll: 2
			}
		}, {
			breakpoint: 586,
			settings: {
				slidesToShow: 1,
				slidesToScroll: 1
			}
		}]
	}), e(".product-4").slick({
		infinite: !0,
		speed: 300,
		slidesToShow: 5,
		slidesToScroll: 1,
		autoplay: !0,
		autoplaySpeed: 3e3,
		responsive: [{
			breakpoint: 1200,
			settings: {
				slidesToShow: 3,
				slidesToScroll: 3
			}
		}, {
			breakpoint: 991,
			settings: {
				slidesToShow: 2,
				slidesToScroll: 2
			}
		}, {
			breakpoint: 400,
			settings: {
				slidesToShow: 1,
				slidesToScroll: 2
			}
		}]
	}), e(".recent-orders").slick({
		infinite: !0,
		speed: 300,
		slidesToShow: 2.1,
		slidesToScroll: 2,
		autoplay: !0,
		autoplaySpeed: 3e3,
		responsive: [{
			breakpoint: 1200,
			settings: {
				slidesToShow: 3,
				slidesToScroll: 3
			}
		}, {
			breakpoint: 991,
			settings: {
				slidesToShow: 2,
				slidesToScroll: 2
			}
		}, {
			breakpoint: 400,
			settings: {
				slidesToShow: 1,
				slidesToScroll: 2
			}
		}]
	}), e(".suppliers-slider").slick({
		dots: !1,
		infinite: !0,
		speed: 300,
		slidesToShow: 5,
		slidesToScroll: 1,
		arrows: !1,
		dots: !1,
		responsive: [{
			breakpoint: 991,
			settings: {
				slidesToShow: 3,
				slidesToScroll: 1,
				infinite: !0,
				dots: !1
			}
		}, {
			breakpoint: 767,
			settings: {
				slidesToShow: 2,
				slidesToScroll: 1,
				dots: !1
			}
		}, {
			breakpoint: 480,
			settings: {
				slidesToShow: 1,
				slidesToScroll: 1,
				dots: !1
			}
		}]
	}), e(".brand-slider").slick({
		infinite: !0,
		speed: 300,
		slidesToShow: 4,
		arrows: !1,
		slidesToScroll: 1,
		autoplay: !0,
		autoplaySpeed: 3e3,
		responsive: [{
			breakpoint: 1367,
			settings: {
				slidesToShow: 3,
				slidesToScroll: 1,
				arrows: !1,
				infinite: !0
			}
		}, {
			breakpoint: 991,
			settings: {
				slidesToShow: 2,
				arrows: !1,
				slidesToScroll: 1
			}
		}, {
			breakpoint: 767,
			settings: {
				slidesToShow: 2,
				arrows: !1,
				slidesToScroll: 1
			}
		}, {
			breakpoint: 480,
			settings: {
				slidesToShow: 1,
				arrows: !1,
				slidesToScroll: 1
			}
		}]
	}), e(".tools-product-4").slick({
		infinite: !0,
		speed: 300,
		slidesToShow: 4,
		slidesToScroll: 4,
		autoplay: !0,
		autoplaySpeed: 5e3,
		responsive: [{
			breakpoint: 1200,
			settings: {
				slidesToShow: 3,
				slidesToScroll: 3
			}
		}, {
			breakpoint: 767,
			settings: {
				slidesToShow: 2,
				slidesToScroll: 2
			}
		}]
	}), e(".product_4").slick({
		infinite: !0,
		speed: 300,
		slidesToShow: 4,
		slidesToScroll: 4,
		autoplay: !0,
		autoplaySpeed: 5e3,
		responsive: [{
			breakpoint: 1430,
			settings: {
				slidesToShow: 3,
				slidesToScroll: 3
			}
		}, {
			breakpoint: 1200,
			settings: {
				slidesToShow: 2,
				slidesToScroll: 2
			}
		}, {
			breakpoint: 991,
			settings: {
				slidesToShow: 3,
				slidesToScroll: 3
			}
		}, {
			breakpoint: 768,
			settings: {
				slidesToShow: 2,
				slidesToScroll: 2
			}
		}]
	}), e(".product-3").slick({
		infinite: !0,
		speed: 300,
		slidesToShow: 3,
		slidesToScroll: 3,
		autoplay: !0,
		autoplaySpeed: 5e3,
		responsive: [{
			breakpoint: 991,
			settings: {
				slidesToShow: 2,
				slidesToScroll: 2
			}
		}]
	}), e(".slide-5").slick({
		dots: !1,
		infinite: !0,
		speed: 300,
		slidesToShow: 5,
		slidesToScroll: 5,
		responsive: [{
			breakpoint: 1367,
			settings: {
				slidesToShow: 4,
				slidesToScroll: 4
			}
		}, {
			breakpoint: 1024,
			settings: {
				slidesToShow: 3,
				slidesToScroll: 3,
				infinite: !0
			}
		}, {
			breakpoint: 600,
			settings: {
				slidesToShow: 3,
				slidesToScroll: 3
			}
		}, {
			breakpoint: 480,
			settings: {
				slidesToShow: 3,
				slidesToScroll: 3
			}
		}]
	}), e(".slide-6").slick({
		dots: !1,
		infinite: !0,
		speed: 300,
		slidesToShow: 8,
		slidesToScroll: 6,
		responsive: [{
			breakpoint: 1367,
			settings: {
				slidesToShow: 5,
				slidesToScroll: 5,
				infinite: !0
			}
		}, {
			breakpoint: 1024,
			settings: {
				slidesToShow: 4,
				slidesToScroll: 4,
				infinite: !0
			}
		}, {
			breakpoint: 767,
			settings: {
				slidesToShow: 3,
				slidesToScroll: 3,
				infinite: !0
			}
		}, {
			breakpoint: 480,
			settings: {
				slidesToShow: 2,
				slidesToScroll: 2
			}
		}]
	}), e(".brand-6").slick({
		dots: !1,
		infinite: !0,
		speed: 300,
		slidesToShow: 6,
		slidesToScroll: 6,
		responsive: [{
			breakpoint: 1367,
			settings: {
				slidesToShow: 5,
				slidesToScroll: 5,
				infinite: !0
			}
		}, {
			breakpoint: 1024,
			settings: {
				slidesToShow: 4,
				slidesToScroll: 4,
				infinite: !0
			}
		}, {
			breakpoint: 767,
			settings: {
				slidesToShow: 3,
				slidesToScroll: 3,
				infinite: !0
			}
		}, {
			breakpoint: 480,
			settings: {
				slidesToShow: 2,
				slidesToScroll: 2
			}
		}, {
			breakpoint: 360,
			settings: {
				slidesToShow: 1,
				slidesToScroll: 1
			}
		}]
	}), e(".product-slider-5").slick({
		dots: !1,
		infinite: !0,
		speed: 300,
		slidesToShow: 5,
		slidesToScroll: 5,
		responsive: [{
			breakpoint: 1200,
			settings: {
				slidesToShow: 3,
				slidesToScroll: 3
			}
		}, {
			breakpoint: 991,
			settings: {
				slidesToShow: 2,
				slidesToScroll: 2
			}
		}]
	}), e(".product-5").slick({
		dots: !1,
		infinite: !0,
		speed: 300,
		slidesToShow: 5,
		slidesToScroll: 5,
		responsive: [{
			breakpoint: 1367,
			settings: {
				slidesToShow: 4,
				slidesToScroll: 4
			}
		}, {
			breakpoint: 1024,
			settings: {
				slidesToShow: 3,
				slidesToScroll: 3,
				infinite: !0
			}
		}, {
			breakpoint: 768,
			settings: {
				slidesToShow: 2,
				slidesToScroll: 2
			}
		}]
	}), e(".slide-7").slick({
		dots: !1,
		infinite: !0,
		speed: 300,
		slidesToShow: 7,
		slidesToScroll: 7,
		responsive: [{
			breakpoint: 1367,
			settings: {
				slidesToShow: 6,
				slidesToScroll: 6
			}
		}, {
			breakpoint: 1024,
			settings: {
				slidesToShow: 5,
				slidesToScroll: 5,
				infinite: !0
			}
		}, {
			breakpoint: 600,
			settings: {
				slidesToShow: 4,
				slidesToScroll: 4
			}
		}, {
			breakpoint: 480,
			settings: {
				slidesToShow: 3,
				slidesToScroll: 3
			}
		}]
	}), e(".slide-8").slick({
		infinite: !0,
		slidesToShow: 8,
		slidesToScroll: 8,
		responsive: [{
			breakpoint: 1200,
			settings: {
				slidesToShow: 6,
				slidesToScroll: 6
			}
		}]
	}), e(".center").slick({
		centerMode: !0,
		centerPadding: "60px",
		slidesToShow: 3,
		responsive: [{
			breakpoint: 768,
			settings: {
				arrows: !1,
				centerMode: !0,
				centerPadding: "40px",
				slidesToShow: 3
			}
		}, {
			breakpoint: 480,
			settings: {
				arrows: !1,
				centerMode: !0,
				centerPadding: "40px",
				slidesToShow: 1
			}
		}]
	}), e(".product-slick").slick({
		slidesToShow: 1,
		slidesToScroll: 1,
		arrows: !0,
		fade: !0,
		asNavFor: ".slider-nav"
	}), e(".slider-nav").slick({
		vertical: !1,
		slidesToShow: 3,
		slidesToScroll: 1,
		asNavFor: ".product-slick",
		arrows: !1,
		dots: !1,
		focusOnSelect: !0
	}), e(".product-right-slick").slick({
		slidesToShow: 1,
		slidesToScroll: 1,
		arrows: !0,
		fade: !0,
		asNavFor: ".slider-right-nav"
	}), e(window).width() > 575 ? e(".slider-right-nav").slick({
		vertical: !0,
		verticalSwiping: !0,
		slidesToShow: 3,
		slidesToScroll: 1,
		asNavFor: ".product-right-slick",
		arrows: !1,
		infinite: !0,
		dots: !1,
		centerMode: !1,
		focusOnSelect: !0
	}) : e(".slider-right-nav").slick({
		vertical: !1,
		verticalSwiping: !1,
		slidesToShow: 3,
		slidesToScroll: 1,
		asNavFor: ".product-right-slick",
		arrows: !1,
		infinite: !0,
		centerMode: !1,
		dots: !1,
		focusOnSelect: !0,
		responsive: [{
			breakpoint: 576,
			settings: {
				slidesToShow: 3,
				slidesToScroll: 1
			}
		}]
	}), e(window).width() < 1199 && (e(".header-2 .navbar .sidebar-bar, .header-2 .navbar .sidebar-back, .header-2 .mobile-search img").on("click", function() {
		e("#mySidenav").hasClass("open-side") ? e(".header-2 #main-nav .toggle-nav").css("z-index", "99") : e(".header-2 #main-nav .toggle-nav").css("z-index", "1")
	}), e(".sidebar-overlay").on("click", function() {
		e(".header-2 #main-nav .toggle-nav").css("z-index", "99")
	}), e(".header-2 #search-overlay .closebtn").on("click", function() {
		e(".header-2 #main-nav .toggle-nav").css("z-index", "99")
	}), e(".layout3-menu .mobile-search .ti-search, .header-2 .mobile-search .ti-search").on("click", function() {
		e(".layout3-menu #main-nav .toggle-nav, .header-2 #main-nav .toggle-nav").css("z-index", "1")
	})), e("#tab-1").css("display", "Block"), e(".default").css("display", "Block"), e(".tabs li a").on("click", function() {
		event.preventDefault(), e(".tab_product_slider").slick("unslick"), e(".product-4").slick("unslick"), e(this).parent().parent().find("li").removeClass("current"), e(this).parent().addClass("current");
		var s = e(this).attr("href");
		e("#" + s).show(), e(this).parent().parent().parent().find(".tab-content").not("#" + s).css("display", "none"), e(".product-4").slick({
			arrows: !0,
			dots: !1,
			infinite: !1,
			speed: 300,
			slidesToShow: 4,
			slidesToScroll: 1,
			responsive: [{
				breakpoint: 1200,
				settings: {
					slidesToShow: 3,
					slidesToScroll: 3
				}
			}, {
				breakpoint: 991,
				settings: {
					slidesToShow: 2,
					slidesToScroll: 2
				}
			}, {
				breakpoint: 420,
				settings: {
					slidesToShow: 1,
					slidesToScroll: 1
				}
			}]
		})
	}), e(".tabs li a").on("click", function() {
		event.preventDefault(), e(".tab_product_slider").slick("unslick"), e(".product-5").slick("unslick"), e(this).parent().parent().find("li").removeClass("current"), e(this).parent().addClass("current");
		var s = e(this).attr("href");
		e("#" + s).show(), e(this).parent().parent().parent().find(".tab-content").not("#" + s).css("display", "none"), e(".product-5").slick({
			arrows: !0,
			dots: !1,
			infinite: !1,
			speed: 300,
			slidesToShow: 5,
			slidesToScroll: 1,
			responsive: [{
				breakpoint: 1367,
				settings: {
					slidesToShow: 4,
					slidesToScroll: 4
				}
			}, {
				breakpoint: 1024,
				settings: {
					slidesToShow: 3,
					slidesToScroll: 3,
					infinite: !0
				}
			}, {
				breakpoint: 768,
				settings: {
					slidesToShow: 2,
					slidesToScroll: 2
				}
			}, {
				breakpoint: 576,
				settings: {
					slidesToShow: 1,
					slidesToScroll: 1
				}
			}]
		})
	}), e("#tab-1").css("display", "Block"), e(".default").css("display", "Block"), e(".tabs li a").on("click", function() {
		event.preventDefault(), e(".tab_product_slider").slick("unslick"), e(".product-3").slick("unslick"), e(this).parent().parent().find("li").removeClass("current"), e(this).parent().addClass("current");
		var s = e(this).attr("href");
		e("#" + s).show(), e(this).parent().parent().parent().parent().find(".tab-content").not("#" + s).css("display", "none"), e(".product-3").slick({
			arrows: !0,
			dots: !1,
			infinite: !1,
			speed: 300,
			slidesToShow: 3,
			slidesToScroll: 1,
			responsive: [{
				breakpoint: 991,
				settings: {
					slidesToShow: 2,
					slidesToScroll: 2
				}
			}]
		})
	}), e(".collapse-block-title").on("click", function(s) {
		s.preventDefault;
		var i = e(this).parent(),
			o = e(this).next(".collection-collapse-block-content");
		i.hasClass("open") ? (i.removeClass("open"), o.slideUp(300)) : (i.addClass("open"), o.slideDown(300))
	}), e(".color-selector ul li").on("click", function(s) {
		e(".color-selector ul li").removeClass("active"), e(this).addClass("active")
	}), e(".list-layout-view").on("click", function(s) {
		e(".collection-grid-view").css("opacity", "0"), e(".product-wrapper-grid").css("opacity", "0.2"), e(".shop-cart-ajax-loader").css("display", "block"), e(".product-wrapper-grid").addClass("list-view"), e(".product-wrapper-grid").children().children().removeClass(), e(".product-wrapper-grid").children().children().addClass("col-lg-12"), setTimeout(function() {
			e(".product-wrapper-grid").css("opacity", "1"), e(".shop-cart-ajax-loader").css("display", "none")
		}, 500)
	}), e(".grid-layout-view").on("click", function(s) {
		e(".collection-grid-view").css("opacity", "1"), e(".product-wrapper-grid").removeClass("list-view"), e(".product-wrapper-grid").children().children().removeClass(), e(".product-wrapper-grid").children().children().addClass("col-lg-3")
	}), e(".product-2-layout-view").on("click", function(s) {
		e(".product-wrapper-grid").hasClass("list-view") || (e(".product-wrapper-grid").children().children().removeClass(), e(".product-wrapper-grid").children().children().addClass("col-lg-6"))
	}), e(".product-3-layout-view").on("click", function(s) {
		e(".product-wrapper-grid").hasClass("list-view") || (e(".product-wrapper-grid").children().children().removeClass(), e(".product-wrapper-grid").children().children().addClass("col-lg-4"))
	}), e(".product-4-layout-view").on("click", function(s) {
		e(".product-wrapper-grid").hasClass("list-view") || (e(".product-wrapper-grid").children().children().removeClass(), e(".product-wrapper-grid").children().children().addClass("col-lg-3"))
	}), e(".product-6-layout-view").on("click", function(s) {
		e(".product-wrapper-grid").hasClass("list-view") || (e(".product-wrapper-grid").children().children().removeClass(), e(".product-wrapper-grid").children().children().addClass("col-lg-2"))
	}), e(".sidebar-popup").on("click", function(s) {
		e(".open-popup").toggleClass("open"), e(".collection-filter").css("left", "-15px")
	}), e(".filter-btn").on("click", function(s) {
		e(".collection-filter").css("left", "-15px"), e("body").toggleClass("overflow-hidden")
	}), e(".filter-back").on("click", function(s) {
		e(".collection-filter").css("left", "-365px"), e(".sidebar-popup").trigger("click")
	}), e(".account-sidebar").on("click", function(s) {
		e(".dashboard-left").css("left", "0")
	}), e(".filter-back").on("click", function(s) {
		e(".dashboard-left").css("left", "-365px")
	}), e(function() {
		e(".product-load-more .col-grid-box").slice(0, 8).show(), e(".loadMore").on("click", function(s) {
			s.preventDefault(), e(".product-load-more .col-grid-box:hidden").slice(0, 4).slideDown(), 0 === e(".product-load-more .col-grid-box:hidden").length && e(".loadMore").text("no more products")
		})
	}), e(".product-box button .ti-shopping-cart").on("click", function() {
		e.notify({
			icon: "fa fa-check",
			title: "Success!",
			message: "Item Successfully added to your cart"
		}, {
			element: "body",
			position: null,
			type: "success",
			allow_dismiss: !0,
			newest_on_top: !1,
			showProgressbar: !0,
			placement: {
				from: "top",
				align: "right"
			},
			offset: 20,
			spacing: 10,
			z_index: 1031,
			delay: 5e3,
			animate: {
				enter: "animated fadeInDown",
				exit: "animated fadeOutUp"
			},
			icon_type: "class",
			template: '<div data-notify="container" class="col-xs-11 col-sm-3 alert alert-{0}" role="alert"><button type="button" aria-hidden="true" class="close" data-notify="dismiss">×</button><span data-notify="icon"></span> <span data-notify="title">{1}</span> <span data-notify="message">{2}</span><div class="progress" data-notify="progressbar"><div class="progress-bar progress-bar-{0}" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div></div><a href="{3}" target="{4}" data-notify="url"></a></div>'
		})
	}), e(".product-box a .ti-heart , .product-box a .fa-heart").on("click", function() {
		e.notify({
			icon: "fa fa-check",
			title: "Success!",
			message: "Item Successfully added in wishlist"
		}, {
			element: "body",
			position: null,
			type: "info",
			allow_dismiss: !0,
			newest_on_top: !1,
			showProgressbar: !0,
			placement: {
				from: "top",
				align: "right"
			},
			offset: 20,
			spacing: 10,
			z_index: 1031,
			delay: 5e3,
			animate: {
				enter: "animated fadeInDown",
				exit: "animated fadeOutUp"
			},
			icon_type: "class",
			template: '<div data-notify="container" class="col-xs-11 col-sm-3 alert alert-{0}" role="alert"><button type="button" aria-hidden="true" class="close" data-notify="dismiss">×</button><span data-notify="icon"></span> <span data-notify="title">{1}</span> <span data-notify="message">{2}</span><div class="progress" data-notify="progressbar"><div class="progress-bar progress-bar-{0}" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div></div><a href="{3}" target="{4}" data-notify="url"></a></div>'
		})
	})
}(jQuery), $("#ltr_btn").click(function() {
	$("body").addClass("ltr"), $("body").removeClass("rtl")
}), $("#rtl_btn").click(function() {
	$("body").addClass("rtl"), $("body").removeClass("ltr")
}), $(".setting_buttons li").click(function() {
	$(this).addClass("active").siblings().removeClass("active")
}), $(".color-box li").click(function() {
	$(this).addClass("active").siblings().removeClass("active")
});
var body_event = $("body");

function openNav() {
	document.getElementById("mySidenav").classList.add("open-side")
}

function closeNav() {
	document.getElementById("mySidenav").classList.remove("open-side")
}

function openSetting() {
	document.getElementById("setting_box").classList.add("open-setting"), document.getElementById("setting-icon").classList.add("open-icon")
}

function closeSetting() {
	document.getElementById("setting_box").classList.remove("open-setting"), document.getElementById("setting-icon").classList.remove("open-icon")
}

function openCart() {
	document.getElementById("cart_side").classList.add("open-side")
}

function closeCart() {
	document.getElementById("cart_side").classList.remove("open-side")
}
body_event.on("click", ".theme-layout-version", function() {
	"Dark" == $(".theme-layout-version").text() ? (localStorage.theme_color = "dark", $("body").addClass("dark"), window.sessionStorage.setItem("theme", "dark"), $(".theme-layout-version").text("Light")) : (localStorage.theme_color = "", $("body").removeClass("dark"), window.sessionStorage.setItem("theme", "light"), $(".theme-layout-version").text("Dark")), $.ajax({
		url: url1,
		type: "POST",
		dataType: "json",
		data: {
			theme_color: localStorage.theme_color
		},
		success: function(e) {}
	})
}), $(function() {}), jQuery(".setting-title h4").append('<span class="according-menu"></span>'), jQuery(".setting-title").on("click", function() {
	jQuery(".setting-title").removeClass("active"), jQuery(".setting-contant").slideUp("normal"), 1 == jQuery(this).next().is(":hidden") && (jQuery(this).addClass("active"), jQuery(this).next().slideDown("normal"))
}), jQuery(".setting-contant").hide(), $(window).on("load", function() {
	$('[data-toggle="tooltip"]').tooltip(), "dark" == localStorage.theme_color && $(".theme-layout-version").text("Light"), $.ajax({
		url: url2,
		type: "GET",
		dataType: "json",
		success: function(e) {
			2 == e.client_preferences.show_dark_mode ? ("dark" == localStorage.theme_color ? $('<div class="sidebar-btn dark-light-btn" id="dark-light-btn-toggle"><div class="theme-layout-version"></div></div>').appendTo($("body")) : $('<div class="sidebar-btn dark-light-btn" id="dark-light-btn-toggle"><div class="theme-layout-version"></div></div>').appendTo($("body")), $("#dark-light-btn-toggle").removeClass("d-none")) : $("#dark-light-btn-toggle").addClass("d-none")
		}
	})
});