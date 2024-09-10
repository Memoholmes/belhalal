/*
Updated by Vinod on 11 MAY 2017
PLEASE NOTE: THIS FILE CONSIST OF THE FOLLOWING FILES

jsbn.js - old one
prng4.js
rng.js
*/
/*
------------------------------------------------------------------------------------------------------------------------
jsbn.js - old one
------------------------------------------------------------------------------------------------------------------------
*/

// Copyright (c) 2005  Tom Wu
// All Rights Reserved.
// See "LICENSE" for details.
// Basic JavaScript BN library - subset useful for RSA encryption.

// Bits per digit
var dbits;

// JavaScript engine analysis
var canary = 0xdeadbeefcafe;
var j_lm = ((canary & 0xffffff) == 0xefcafe);

// (public) Constructor

function BigInteger(a, b, c) {
    if (a != null) if ("number" == typeof a) this.fromNumber(a, b, c);
    else if (b == null && "string" != typeof a) this.fromString(a, 256);
    else this.fromString(a, b);
}

// return new, unset BigInteger

function nbi() {
    return new BigInteger(null);
}

// am: Compute w_j += (x*this_i), propagate carries,
// c is initial carry, returns final carry.
// c < 3*dvalue, x < 2*dvalue, this_i < dvalue
// We need to select the fastest one that works in this environment.
// am1: use a single mult and divide to get the high bits,
// max digit bits should be 26 because
// max internal value = 2*dvalue^2-2*dvalue (< 2^53)

function am1(i, x, w, j, c, n) {
    while (--n >= 0) {
        var v = x * this[i++] + w[j] + c;
        c = Math.floor(v / 0x4000000);
        w[j++] = v & 0x3ffffff;
    }
    return c;
}
// am2 avoids a big mult-and-extract completely.
// Max digit bits should be <= 30 because we do bitwise ops
// on values up to 2*hdvalue^2-hdvalue-1 (< 2^31)

function am2(i, x, w, j, c, n) {
    var xl = x & 0x7fff,
        xh = x >> 15;
    while (--n >= 0) {
        var l = this[i] & 0x7fff;
        var h = this[i++] >> 15;
        var m = xh * l + h * xl;
        l = xl * l + ((m & 0x7fff) << 15) + w[j] + (c & 0x3fffffff);
        c = (l >>> 30) + (m >>> 15) + xh * h + (c >>> 30);
        w[j++] = l & 0x3fffffff;
    }
    return c;
}
// Alternately, set max digit bits to 28 since some
// browsers slow down when dealing with 32-bit numbers.

function am3(i, x, w, j, c, n) {
    var xl = x & 0x3fff,
        xh = x >> 14;
    while (--n >= 0) {
        var l = this[i] & 0x3fff;
        var h = this[i++] >> 14;
        var m = xh * l + h * xl;
        l = xl * l + ((m & 0x3fff) << 14) + w[j] + c;
        c = (l >> 28) + (m >> 14) + xh * h;
        w[j++] = l & 0xfffffff;
    }
    return c;
}
if (j_lm && (navigator.appName == "Microsoft Internet Explorer")) {
    BigInteger.prototype.am = am2;
    dbits = 30;
}
else if (j_lm && (navigator.appName != "Netscape")) {
    BigInteger.prototype.am = am1;
    dbits = 26;
}
else { // Mozilla/Netscape seems to prefer am3
    BigInteger.prototype.am = am3;
    dbits = 28;
}

BigInteger.prototype.DB = dbits;
BigInteger.prototype.DM = ((1 << dbits) - 1);
BigInteger.prototype.DV = (1 << dbits);

var BI_FP = 52;
BigInteger.prototype.FV = Math.pow(2, BI_FP);
BigInteger.prototype.F1 = BI_FP - dbits;
BigInteger.prototype.F2 = 2 * dbits - BI_FP;

// Digit conversions
var BI_RM = "0123456789abcdefghijklmnopqrstuvwxyz";
var BI_RC = new Array();
var rr, vv;
rr = "0".charCodeAt(0);
for (vv = 0; vv <= 9; ++vv) BI_RC[rr++] = vv;
rr = "a".charCodeAt(0);
for (vv = 10; vv < 36; ++vv) BI_RC[rr++] = vv;
rr = "A".charCodeAt(0);
for (vv = 10; vv < 36; ++vv) BI_RC[rr++] = vv;

function int2char(n) {
    return BI_RM.charAt(n);
}

function intAt(s, i) {
    var c = BI_RC[s.charCodeAt(i)];
    return (c == null) ? -1 : c;
}

// (protected) copy this to r

function bnpCopyTo(r) {
    for (var i = this.t - 1; i >= 0; --i) r[i] = this[i];
    r.t = this.t;
    r.s = this.s;
}

// (protected) set from integer value x, -DV <= x < DV

function bnpFromInt(x) {
    this.t = 1;
    this.s = (x < 0) ? -1 : 0;
    if (x > 0) this[0] = x;
    else if (x < -1) this[0] = x + DV;
    else this.t = 0;
}

// return bigint initialized to value

function nbv(i) {
    var r = nbi();
    r.fromInt(i);
    return r;
}

// (protected) set from string and radix

function bnpFromString(s, b) {
    var k;
    if (b == 16) k = 4;
    else if (b == 8) k = 3;
    else if (b == 256) k = 8; // byte array
    else if (b == 2) k = 1;
    else if (b == 32) k = 5;
    else if (b == 4) k = 2;
    else {
        this.fromRadix(s, b);
        return;
    }
    this.t = 0;
    this.s = 0;
    var i = s.length,
        mi = false,
        sh = 0;
    while (--i >= 0) {
        var x = (k == 8) ? s[i] & 0xff : intAt(s, i);
        if (x < 0) {
            if (s.charAt(i) == "-") mi = true;
            continue;
        }
        mi = false;
        if (sh == 0) this[this.t++] = x;
        else if (sh + k > this.DB) {
            this[this.t - 1] |= (x & ((1 << (this.DB - sh)) - 1)) << sh;
            this[this.t++] = (x >> (this.DB - sh));
        }
        else this[this.t - 1] |= x << sh;
        sh += k;
        if (sh >= this.DB) sh -= this.DB;
    }
    if (k == 8 && (s[0] & 0x80) != 0) {
        this.s = -1;
        if (sh > 0) this[this.t - 1] |= ((1 << (this.DB - sh)) - 1) << sh;
    }
    this.clamp();
    if (mi) BigInteger.ZERO.subTo(this, this);
}

// (protected) clamp off excess high words

function bnpClamp() {
    var c = this.s & this.DM;
    while (this.t > 0 && this[this.t - 1] == c) --this.t;
}

// (public) return string representation in given radix

function bnToString(b) {
    if (this.s < 0) return "-" + this.negate().toString(b);
    var k;
    if (b == 16) k = 4;
    else if (b == 8) k = 3;
    else if (b == 2) k = 1;
    else if (b == 32) k = 5;
    else if (b == 64) k = 6;
    else if (b == 4) k = 2;
    else return this.toRadix(b);
    var km = (1 << k) - 1,
        d, m = false,
        r = "",
        i = this.t;
    var p = this.DB - (i * this.DB) % k;
    if (i-- > 0) {
        if (p < this.DB && (d = this[i] >> p) > 0) {
            m = true;
            r = int2char(d);
        }
        while (i >= 0) {
            if (p < k) {
                d = (this[i] & ((1 << p) - 1)) << (k - p);
                d |= this[--i] >> (p += this.DB - k);
            }
            else {
                d = (this[i] >> (p -= k)) & km;
                if (p <= 0) {
                    p += this.DB;
                    --i;
                }
            }
            if (d > 0) m = true;
            if (m) r += int2char(d);
        }
    }
    return m ? r : "0";
}

// (public) -this

function bnNegate() {
    var r = nbi();
    BigInteger.ZERO.subTo(this, r);
    return r;
}

// (public) |this|

function bnAbs() {
    return (this.s < 0) ? this.negate() : this;
}

// (public) return + if this > a, - if this < a, 0 if equal

function bnCompareTo(a) {
    var r = this.s - a.s;
    if (r != 0) return r;
    var i = this.t;
    r = i - a.t;
    if (r != 0) return r;
    while (--i >= 0) if ((r = this[i] - a[i]) != 0) return r;
    return 0;
}

// returns bit length of the integer x

function nbits(x) {
    var r = 1,
        t;
    if ((t = x >>> 16) != 0) {
        x = t;
        r += 16;
    }
    if ((t = x >> 8) != 0) {
        x = t;
        r += 8;
    }
    if ((t = x >> 4) != 0) {
        x = t;
        r += 4;
    }
    if ((t = x >> 2) != 0) {
        x = t;
        r += 2;
    }
    if ((t = x >> 1) != 0) {
        x = t;
        r += 1;
    }
    return r;
}

// (public) return the number of bits in "this"

function bnBitLength() {
    if (this.t <= 0) return 0;
    return this.DB * (this.t - 1) + nbits(this[this.t - 1] ^ (this.s & this.DM));
}

// (protected) r = this << n*DB

function bnpDLShiftTo(n, r) {
    var i;
    for (i = this.t - 1; i >= 0; --i) r[i + n] = this[i];
    for (i = n - 1; i >= 0; --i) r[i] = 0;
    r.t = this.t + n;
    r.s = this.s;
}

// (protected) r = this >> n*DB

function bnpDRShiftTo(n, r) {
    for (var i = n; i < this.t; ++i) r[i - n] = this[i];
    r.t = Math.max(this.t - n, 0);
    r.s = this.s;
}

// (protected) r = this << n

function bnpLShiftTo(n, r) {
    var bs = n % this.DB;
    var cbs = this.DB - bs;
    var bm = (1 << cbs) - 1;
    var ds = Math.floor(n / this.DB),
        c = (this.s << bs) & this.DM,
        i;
    for (i = this.t - 1; i >= 0; --i) {
        r[i + ds + 1] = (this[i] >> cbs) | c;
        c = (this[i] & bm) << bs;
    }
    for (i = ds - 1; i >= 0; --i) r[i] = 0;
    r[ds] = c;
    r.t = this.t + ds + 1;
    r.s = this.s;
    r.clamp();
}

// (protected) r = this >> n

function bnpRShiftTo(n, r) {
    r.s = this.s;
    var ds = Math.floor(n / this.DB);
    if (ds >= this.t) {
        r.t = 0;
        return;
    }
    var bs = n % this.DB;
    var cbs = this.DB - bs;
    var bm = (1 << bs) - 1;
    r[0] = this[ds] >> bs;
    for (var i = ds + 1; i < this.t; ++i) {
        r[i - ds - 1] |= (this[i] & bm) << cbs;
        r[i - ds] = this[i] >> bs;
    }
    if (bs > 0) r[this.t - ds - 1] |= (this.s & bm) << cbs;
    r.t = this.t - ds;
    r.clamp();
}

// (protected) r = this - a

function bnpSubTo(a, r) {
    var i = 0,
        c = 0,
        m = Math.min(a.t, this.t);
    while (i < m) {
        c += this[i] - a[i];
        r[i++] = c & this.DM;
        c >>= this.DB;
    }
    if (a.t < this.t) {
        c -= a.s;
        while (i < this.t) {
            c += this[i];
            r[i++] = c & this.DM;
            c >>= this.DB;
        }
        c += this.s;
    }
    else {
        c += this.s;
        while (i < a.t) {
            c -= a[i];
            r[i++] = c & this.DM;
            c >>= this.DB;
        }
        c -= a.s;
    }
    r.s = (c < 0) ? -1 : 0;
    if (c < -1) r[i++] = this.DV + c;
    else if (c > 0) r[i++] = c;
    r.t = i;
    r.clamp();
}

// (protected) r = this * a, r != this,a (HAC 14.12)
// "this" should be the larger one if appropriate.

function bnpMultiplyTo(a, r) {
    var x = this.abs(),
        y = a.abs();
    var i = x.t;
    r.t = i + y.t;
    while (--i >= 0) r[i] = 0;
    for (i = 0; i < y.t; ++i) r[i + x.t] = x.am(0, y[i], r, i, 0, x.t);
    r.s = 0;
    r.clamp();
    if (this.s != a.s) BigInteger.ZERO.subTo(r, r);
}

// (protected) r = this^2, r != this (HAC 14.16)

function bnpSquareTo(r) {
    var x = this.abs();
    var i = r.t = 2 * x.t;
    while (--i >= 0) r[i] = 0;
    for (i = 0; i < x.t - 1; ++i) {
        var c = x.am(i, x[i], r, 2 * i, 0, 1);
        if ((r[i + x.t] += x.am(i + 1, 2 * x[i], r, 2 * i + 1, c, x.t - i - 1)) >= x.DV) {
            r[i + x.t] -= x.DV;
            r[i + x.t + 1] = 1;
        }
    }
    if (r.t > 0) r[r.t - 1] += x.am(i, x[i], r, 2 * i, 0, 1);
    r.s = 0;
    r.clamp();
}

// (protected) divide this by m, quotient and remainder to q, r (HAC 14.20)
// r != q, this != m.  q or r may be null.

function bnpDivRemTo(m, q, r) {
    var pm = m.abs();
    if (pm.t <= 0) return;
    var pt = this.abs();
    if (pt.t < pm.t) {
        if (q != null) q.fromInt(0);
        if (r != null) this.copyTo(r);
        return;
    }
    if (r == null) r = nbi();
    var y = nbi(),
        ts = this.s,
        ms = m.s;
    var nsh = this.DB - nbits(pm[pm.t - 1]); // normalize modulus
    if (nsh > 0) {
        pm.lShiftTo(nsh, y);
        pt.lShiftTo(nsh, r);
    }
    else {
        pm.copyTo(y);
        pt.copyTo(r);
    }
    var ys = y.t;
    var y0 = y[ys - 1];
    if (y0 == 0) return;
    var yt = y0 * (1 << this.F1) + ((ys > 1) ? y[ys - 2] >> this.F2 : 0);
    var d1 = this.FV / yt,
        d2 = (1 << this.F1) / yt,
        e = 1 << this.F2;
    var i = r.t,
        j = i - ys,
        t = (q == null) ? nbi() : q;
    y.dlShiftTo(j, t);
    if (r.compareTo(t) >= 0) {
        r[r.t++] = 1;
        r.subTo(t, r);
    }
    BigInteger.ONE.dlShiftTo(ys, t);
    t.subTo(y, y); // "negative" y so we can replace sub with am later
    while (y.t < ys) y[y.t++] = 0;
    while (--j >= 0) {
        // Estimate quotient digit
        var qd = (r[--i] == y0) ? this.DM : Math.floor(r[i] * d1 + (r[i - 1] + e) * d2);
        if ((r[i] += y.am(0, qd, r, j, 0, ys)) < qd) { // Try it out
            y.dlShiftTo(j, t);
            r.subTo(t, r);
            while (r[i] < --qd) r.subTo(t, r);
        }
    }
    if (q != null) {
        r.drShiftTo(ys, q);
        if (ts != ms) BigInteger.ZERO.subTo(q, q);
    }
    r.t = ys;
    r.clamp();
    if (nsh > 0) r.rShiftTo(nsh, r); // Denormalize remainder
    if (ts < 0) BigInteger.ZERO.subTo(r, r);
}

// (public) this mod a

function bnMod(a) {
    var r = nbi();
    this.abs().divRemTo(a, null, r);
    if (this.s < 0 && r.compareTo(BigInteger.ZERO) > 0) a.subTo(r, r);
    return r;
}

// Modular reduction using "classic" algorithm

function Classic(m) {
    this.m = m;
}

function cConvert(x) {
    if (x.s < 0 || x.compareTo(this.m) >= 0) return x.mod(this.m);
    else return x;
}

function cRevert(x) {
    return x;
}

function cReduce(x) {
    x.divRemTo(this.m, null, x);
}

function cMulTo(x, y, r) {
    x.multiplyTo(y, r);
    this.reduce(r);
}

function cSqrTo(x, r) {
    x.squareTo(r);
    this.reduce(r);
}

Classic.prototype.convert = cConvert;
Classic.prototype.revert = cRevert;
Classic.prototype.reduce = cReduce;
Classic.prototype.mulTo = cMulTo;
Classic.prototype.sqrTo = cSqrTo;

// (protected) return "-1/this % 2^DB"; useful for Mont. reduction
// justification:
//         xy == 1 (mod m)
//         xy =  1+km
//   xy(2-xy) = (1+km)(1-km)
// x[y(2-xy)] = 1-k^2m^2
// x[y(2-xy)] == 1 (mod m^2)
// if y is 1/x mod m, then y(2-xy) is 1/x mod m^2
// should reduce x and y(2-xy) by m^2 at each step to keep size bounded.
// JS multiply "overflows" differently from C/C++, so care is needed here.

function bnpInvDigit() {
    if (this.t < 1) return 0;
    var x = this[0];
    if ((x & 1) == 0) return 0;
    var y = x & 3; // y == 1/x mod 2^2
    y = (y * (2 - (x & 0xf) * y)) & 0xf; // y == 1/x mod 2^4
    y = (y * (2 - (x & 0xff) * y)) & 0xff; // y == 1/x mod 2^8
    y = (y * (2 - (((x & 0xffff) * y) & 0xffff))) & 0xffff; // y == 1/x mod 2^16
    // last step - calculate inverse mod DV directly;
    // assumes 16 < DB <= 32 and assumes ability to handle 48-bit ints
    y = (y * (2 - x * y % this.DV)) % this.DV; // y == 1/x mod 2^dbits
    // we really want the negative inverse, and -DV < y < DV
    return (y > 0) ? this.DV - y : -y;
}

// Montgomery reduction

function Montgomery(m) {
    this.m = m;
    this.mp = m.invDigit();
    this.mpl = this.mp & 0x7fff;
    this.mph = this.mp >> 15;
    this.um = (1 << (m.DB - 15)) - 1;
    this.mt2 = 2 * m.t;
}

// xR mod m

function montConvert(x) {
    var r = nbi();
    x.abs().dlShiftTo(this.m.t, r);
    r.divRemTo(this.m, null, r);
    if (x.s < 0 && r.compareTo(BigInteger.ZERO) > 0) this.m.subTo(r, r);
    return r;
}

// x/R mod m

function montRevert(x) {
    var r = nbi();
    x.copyTo(r);
    this.reduce(r);
    return r;
}

// x = x/R mod m (HAC 14.32)

function montReduce(x) {
    while (x.t <= this.mt2) // pad x so am has enough room later
        x[x.t++] = 0;
    for (var i = 0; i < this.m.t; ++i) {
        // faster way of calculating u0 = x[i]*mp mod DV
        var j = x[i] & 0x7fff;
        var u0 = (j * this.mpl + (((j * this.mph + (x[i] >> 15) * this.mpl) & this.um) << 15)) & x.DM;
        // use am to combine the multiply-shift-add into one call
        j = i + this.m.t;
        x[j] += this.m.am(0, u0, x, i, 0, this.m.t);
        // propagate carry
        while (x[j] >= x.DV) {
            x[j] -= x.DV;
            x[++j]++;
        }
    }
    x.clamp();
    x.drShiftTo(this.m.t, x);
    if (x.compareTo(this.m) >= 0) x.subTo(this.m, x);
}

// r = "x^2/R mod m"; x != r

function montSqrTo(x, r) {
    x.squareTo(r);
    this.reduce(r);
}

// r = "xy/R mod m"; x,y != r

function montMulTo(x, y, r) {
    x.multiplyTo(y, r);
    this.reduce(r);
}

Montgomery.prototype.convert = montConvert;
Montgomery.prototype.revert = montRevert;
Montgomery.prototype.reduce = montReduce;
Montgomery.prototype.mulTo = montMulTo;
Montgomery.prototype.sqrTo = montSqrTo;

// (protected) true iff this is even

function bnpIsEven() {
    return ((this.t > 0) ? (this[0] & 1) : this.s) == 0;
}

// (protected) this^e, e < 2^32, doing sqr and mul with "r" (HAC 14.79)

function bnpExp(e, z) {
    if (e > 0xffffffff || e < 1) return BigInteger.ONE;
    var r = nbi(),
        r2 = nbi(),
        g = z.convert(this),
        i = nbits(e) - 1;
    g.copyTo(r);
    while (--i >= 0) {
        z.sqrTo(r, r2);
        if ((e & (1 << i)) > 0) z.mulTo(r2, g, r);
        else {
            var t = r;
            r = r2;
            r2 = t;
        }
    }
    return z.revert(r);
}

// (public) this^e % m, 0 <= e < 2^32

function bnModPowInt(e, m) {
    var z;
    if (e < 256 || m.isEven()) z = new Classic(m);
    else z = new Montgomery(m);
    return this.exp(e, z);
}

// protected
BigInteger.prototype.copyTo = bnpCopyTo;
BigInteger.prototype.fromInt = bnpFromInt;
BigInteger.prototype.fromString = bnpFromString;
BigInteger.prototype.clamp = bnpClamp;
BigInteger.prototype.dlShiftTo = bnpDLShiftTo;
BigInteger.prototype.drShiftTo = bnpDRShiftTo;
BigInteger.prototype.lShiftTo = bnpLShiftTo;
BigInteger.prototype.rShiftTo = bnpRShiftTo;
BigInteger.prototype.subTo = bnpSubTo;
BigInteger.prototype.multiplyTo = bnpMultiplyTo;
BigInteger.prototype.squareTo = bnpSquareTo;
BigInteger.prototype.divRemTo = bnpDivRemTo;
BigInteger.prototype.invDigit = bnpInvDigit;
BigInteger.prototype.isEven = bnpIsEven;
BigInteger.prototype.exp = bnpExp;

// public
BigInteger.prototype.toString = bnToString;
BigInteger.prototype.negate = bnNegate;
BigInteger.prototype.abs = bnAbs;
BigInteger.prototype.compareTo = bnCompareTo;
BigInteger.prototype.bitLength = bnBitLength;
BigInteger.prototype.mod = bnMod;
BigInteger.prototype.modPowInt = bnModPowInt;

// "constants"
BigInteger.ZERO = nbv(0);
BigInteger.ONE = nbv(1);


function bnClone() {
    var r = nbi();
    this.copyTo(r);
    return r;
}

// (public) return value as integer

function bnIntValue() {
    if (this.s < 0) {
        if (this.t == 1) return this[0] - this.DV;
        else if (this.t == 0) return -1;
    }
    else if (this.t == 1) return this[0];
    else if (this.t == 0) return 0;
    // assumes 16 < DB < 32
    return ((this[1] & ((1 << (32 - this.DB)) - 1)) << this.DB) | this[0];
}

// (public) return value as byte

function bnByteValue() {
    return (this.t == 0) ? this.s : (this[0] << 24) >> 24;
}

// (public) return value as short (assumes DB>=16)

function bnShortValue() {
    return (this.t == 0) ? this.s : (this[0] << 16) >> 16;
}

// (protected) return x s.t. r^x < DV

function bnpChunkSize(r) {
    return Math.floor(Math.LN2 * this.DB / Math.log(r));
}

// (public) 0 if this == 0, 1 if this > 0

function bnSigNum() {
    if (this.s < 0) return -1;
    else if (this.t <= 0 || (this.t == 1 && this[0] <= 0)) return 0;
    else return 1;
}

// (protected) convert to radix string

function bnpToRadix(b) {
    if (b == null) b = 10;
    if (this.signum() == 0 || b < 2 || b > 36) return "0";
    var cs = this.chunkSize(b);
    var a = Math.pow(b, cs);
    var d = nbv(a),
        y = nbi(),
        z = nbi(),
        r = "";
    this.divRemTo(d, y, z);
    while (y.signum() > 0) {
        r = (a + z.intValue()).toString(b).substr(1) + r;
        y.divRemTo(d, y, z);
    }
    return z.intValue().toString(b) + r;
}

// (protected) convert from radix string

function bnpFromRadix(s, b) {
    this.fromInt(0);
    if (b == null) b = 10;
    var cs = this.chunkSize(b);
    var d = Math.pow(b, cs),
        mi = false,
        j = 0,
        w = 0;
    for (var i = 0; i < s.length; ++i) {
        var x = intAt(s, i);
        if (x < 0) {
            if (s.charAt(i) == "-" && this.signum() == 0) mi = true;
            continue;
        }
        w = b * w + x;
        if (++j >= cs) {
            this.dMultiply(d);
            this.dAddOffset(w, 0);
            j = 0;
            w = 0;
        }
    }
    if (j > 0) {
        this.dMultiply(Math.pow(b, j));
        this.dAddOffset(w, 0);
    }
    if (mi) BigInteger.ZERO.subTo(this, this);
}

// (protected) alternate constructor

function bnpFromNumber(a, b, c) {
    if ("number" == typeof b) {
        // new BigInteger(int,int,RNG)
        if (a < 2) this.fromInt(1);
        else {
            this.fromNumber(a, c);
            if (!this.testBit(a - 1)) // force MSB set
                this.bitwiseTo(BigInteger.ONE.shiftLeft(a - 1), op_or, this);
            if (this.isEven()) this.dAddOffset(1, 0); // force odd
            while (!this.isProbablePrime(b)) {
                this.dAddOffset(2, 0);
                if (this.bitLength() > a) this.subTo(BigInteger.ONE.shiftLeft(a - 1), this);
            }
        }
    }
    else {
        // new BigInteger(int,RNG)
        var x = new Array(),
            t = a & 7;
        x.length = (a >> 3) + 1;
        b.nextBytes(x);
        if (t > 0) x[0] &= ((1 << t) - 1);
        else x[0] = 0;
        this.fromString(x, 256);
    }
}

// (public) convert to bigendian byte array

function bnToByteArray() {
    var i = this.t,
        r = new Array();
    r[0] = this.s;
    var p = this.DB - (i * this.DB) % 8,
        d, k = 0;
    if (i-- > 0) {
        if (p < this.DB && (d = this[i] >> p) != (this.s & this.DM) >> p) r[k++] = d | (this.s << (this.DB - p));
        while (i >= 0) {
            if (p < 8) {
                d = (this[i] & ((1 << p) - 1)) << (8 - p);
                d |= this[--i] >> (p += this.DB - 8);
            }
            else {
                d = (this[i] >> (p -= 8)) & 0xff;
                if (p <= 0) {
                    p += this.DB;
                    --i;
                }
            }
            if ((d & 0x80) != 0) d |= -256;
            if (k == 0 && (this.s & 0x80) != (d & 0x80)) ++k;
            if (k > 0 || d != this.s) r[k++] = d;
        }
    }
    return r;
}

function bnEquals(a) {
    return (this.compareTo(a) == 0);
}

function bnMin(a) {
    return (this.compareTo(a) < 0) ? this : a;
}

function bnMax(a) {
    return (this.compareTo(a) > 0) ? this : a;
}

// (protected) r = this op a (bitwise)

function bnpBitwiseTo(a, op, r) {
    var i, f, m = Math.min(a.t, this.t);
    for (i = 0; i < m; ++i) r[i] = op(this[i], a[i]);
    if (a.t < this.t) {
        f = a.s & this.DM;
        for (i = m; i < this.t; ++i) r[i] = op(this[i], f);
        r.t = this.t;
    }
    else {
        f = this.s & this.DM;
        for (i = m; i < a.t; ++i) r[i] = op(f, a[i]);
        r.t = a.t;
    }
    r.s = op(this.s, a.s);
    r.clamp();
}

// (public) this & a

function op_and(x, y) {
    return x & y;
}

function bnAnd(a) {
    var r = nbi();
    this.bitwiseTo(a, op_and, r);
    return r;
}

// (public) this | a

function op_or(x, y) {
    return x | y;
}

function bnOr(a) {
    var r = nbi();
    this.bitwiseTo(a, op_or, r);
    return r;
}

// (public) this ^ a

function op_xor(x, y) {
    return x ^ y;
}

function bnXor(a) {
    var r = nbi();
    this.bitwiseTo(a, op_xor, r);
    return r;
}

// (public) this & ~a

function op_andnot(x, y) {
    return x & ~y;
}

function bnAndNot(a) {
    var r = nbi();
    this.bitwiseTo(a, op_andnot, r);
    return r;
}

// (public) ~this

function bnNot() {
    var r = nbi();
    for (var i = 0; i < this.t; ++i) r[i] = this.DM & ~this[i];
    r.t = this.t;
    r.s = ~this.s;
    return r;
}

// (public) this << n

function bnShiftLeft(n) {
    var r = nbi();
    if (n < 0) this.rShiftTo(-n, r);
    else this.lShiftTo(n, r);
    return r;
}

// (public) this >> n

function bnShiftRight(n) {
    var r = nbi();
    if (n < 0) this.lShiftTo(-n, r);
    else this.rShiftTo(n, r);
    return r;
}

// return index of lowest 1-bit in x, x < 2^31

function lbit(x) {
    if (x == 0) return -1;
    var r = 0;
    if ((x & 0xffff) == 0) {
        x >>= 16;
        r += 16;
    }
    if ((x & 0xff) == 0) {
        x >>= 8;
        r += 8;
    }
    if ((x & 0xf) == 0) {
        x >>= 4;
        r += 4;
    }
    if ((x & 3) == 0) {
        x >>= 2;
        r += 2;
    }
    if ((x & 1) == 0) ++r;
    return r;
}

// (public) returns index of lowest 1-bit (or -1 if none)

function bnGetLowestSetBit() {
    for (var i = 0; i < this.t; ++i)
        if (this[i] != 0) return i * this.DB + lbit(this[i]);
    if (this.s < 0) return this.t * this.DB;
    return -1;
}

// return number of 1 bits in x

function cbit(x) {
    var r = 0;
    while (x != 0) {
        x &= x - 1;
        ++r;
    }
    return r;
}

// (public) return number of set bits

function bnBitCount() {
    var r = 0,
        x = this.s & this.DM;
    for (var i = 0; i < this.t; ++i) r += cbit(this[i] ^ x);
    return r;
}

// (public) true iff nth bit is set

function bnTestBit(n) {
    var j = Math.floor(n / this.DB);
    if (j >= this.t) return (this.s != 0);
    return ((this[j] & (1 << (n % this.DB))) != 0);
}

// (protected) this op (1<<n)

function bnpChangeBit(n, op) {
    var r = BigInteger.ONE.shiftLeft(n);
    this.bitwiseTo(r, op, r);
    return r;
}

// (public) this | (1<<n)

function bnSetBit(n) {
    return this.changeBit(n, op_or);
}

// (public) this & ~(1<<n)

function bnClearBit(n) {
    return this.changeBit(n, op_andnot);
}

// (public) this ^ (1<<n)

function bnFlipBit(n) {
    return this.changeBit(n, op_xor);
}

// (protected) r = this + a

function bnpAddTo(a, r) {
    var i = 0,
        c = 0,
        m = Math.min(a.t, this.t);
    while (i < m) {
        c += this[i] + a[i];
        r[i++] = c & this.DM;
        c >>= this.DB;
    }
    if (a.t < this.t) {
        c += a.s;
        while (i < this.t) {
            c += this[i];
            r[i++] = c & this.DM;
            c >>= this.DB;
        }
        c += this.s;
    }
    else {
        c += this.s;
        while (i < a.t) {
            c += a[i];
            r[i++] = c & this.DM;
            c >>= this.DB;
        }
        c += a.s;
    }
    r.s = (c < 0) ? -1 : 0;
    if (c > 0) r[i++] = c;
    else if (c < -1) r[i++] = this.DV + c;
    r.t = i;
    r.clamp();
}

// (public) this + a

function bnAdd(a) {
    var r = nbi();
    this.addTo(a, r);
    return r;
}

// (public) this - a

function bnSubtract(a) {
    var r = nbi();
    this.subTo(a, r);
    return r;
}

// (public) this * a

function bnMultiply(a) {
    var r = nbi();
    this.multiplyTo(a, r);
    return r;
}

// (public) this^2

function bnSquare() {
    var r = nbi();
    this.squareTo(r);
    return r;
}

// (public) this / a

function bnDivide(a) {
    var r = nbi();
    this.divRemTo(a, r, null);
    return r;
}

// (public) this % a

function bnRemainder(a) {
    var r = nbi();
    this.divRemTo(a, null, r);
    return r;
}

// (public) [this/a,this%a]

function bnDivideAndRemainder(a) {
    var q = nbi(),
        r = nbi();
    this.divRemTo(a, q, r);
    return new Array(q, r);
}

// (protected) this *= n, this >= 0, 1 < n < DV

function bnpDMultiply(n) {
    this[this.t] = this.am(0, n - 1, this, 0, 0, this.t);
    ++this.t;
    this.clamp();
}

// (protected) this += n << w words, this >= 0

function bnpDAddOffset(n, w) {
    if (n == 0) return;
    while (this.t <= w) this[this.t++] = 0;
    this[w] += n;
    while (this[w] >= this.DV) {
        this[w] -= this.DV;
        if (++w >= this.t) this[this.t++] = 0;
        ++this[w];
    }
}

// A "null" reducer

function NullExp() { }

function nNop(x) {
    return x;
}

function nMulTo(x, y, r) {
    x.multiplyTo(y, r);
}

function nSqrTo(x, r) {
    x.squareTo(r);
}

NullExp.prototype.convert = nNop;
NullExp.prototype.revert = nNop;
NullExp.prototype.mulTo = nMulTo;
NullExp.prototype.sqrTo = nSqrTo;

// (public) this^e

function bnPow(e) {
    return this.exp(e, new NullExp());
}

// (protected) r = lower n words of "this * a", a.t <= n
// "this" should be the larger one if appropriate.

function bnpMultiplyLowerTo(a, n, r) {
    var i = Math.min(this.t + a.t, n);
    r.s = 0; // assumes a,this >= 0
    r.t = i;
    while (i > 0) r[--i] = 0;
    var j;
    for (j = r.t - this.t; i < j; ++i) r[i + this.t] = this.am(0, a[i], r, i, 0, this.t);
    for (j = Math.min(a.t, n); i < j; ++i) this.am(0, a[i], r, i, 0, n - i);
    r.clamp();
}

// (protected) r = "this * a" without lower n words, n > 0
// "this" should be the larger one if appropriate.

function bnpMultiplyUpperTo(a, n, r) {
    --n;
    var i = r.t = this.t + a.t - n;
    r.s = 0; // assumes a,this >= 0
    while (--i >= 0) r[i] = 0;
    for (i = Math.max(n - this.t, 0); i < a.t; ++i)
        r[this.t + i - n] = this.am(n - i, a[i], r, 0, 0, this.t + i - n);
    r.clamp();
    r.drShiftTo(1, r);
}

// Barrett modular reduction

function Barrett(m) {
    // setup Barrett
    this.r2 = nbi();
    this.q3 = nbi();
    BigInteger.ONE.dlShiftTo(2 * m.t, this.r2);
    this.mu = this.r2.divide(m);
    this.m = m;
}

function barrettConvert(x) {
    if (x.s < 0 || x.t > 2 * this.m.t) return x.mod(this.m);
    else if (x.compareTo(this.m) < 0) return x;
    else {
        var r = nbi();
        x.copyTo(r);
        this.reduce(r);
        return r;
    }
}

function barrettRevert(x) {
    return x;
}

// x = x mod m (HAC 14.42)

function barrettReduce(x) {
    x.drShiftTo(this.m.t - 1, this.r2);
    if (x.t > this.m.t + 1) {
        x.t = this.m.t + 1;
        x.clamp();
    }
    this.mu.multiplyUpperTo(this.r2, this.m.t + 1, this.q3);
    this.m.multiplyLowerTo(this.q3, this.m.t + 1, this.r2);
    while (x.compareTo(this.r2) < 0) x.dAddOffset(1, this.m.t + 1);
    x.subTo(this.r2, x);
    while (x.compareTo(this.m) >= 0) x.subTo(this.m, x);
}

// r = x^2 mod m; x != r

function barrettSqrTo(x, r) {
    x.squareTo(r);
    this.reduce(r);
}

// r = x*y mod m; x,y != r

function barrettMulTo(x, y, r) {
    x.multiplyTo(y, r);
    this.reduce(r);
}

Barrett.prototype.convert = barrettConvert;
Barrett.prototype.revert = barrettRevert;
Barrett.prototype.reduce = barrettReduce;
Barrett.prototype.mulTo = barrettMulTo;
Barrett.prototype.sqrTo = barrettSqrTo;

// (public) this^e % m (HAC 14.85)

function bnModPow(e, m) {
    var i = e.bitLength(),
        k, r = nbv(1),
        z;
    if (i <= 0) return r;
    else if (i < 18) k = 1;
    else if (i < 48) k = 3;
    else if (i < 144) k = 4;
    else if (i < 768) k = 5;
    else k = 6;
    if (i < 8) z = new Classic(m);
    else if (m.isEven()) z = new Barrett(m);
    else z = new Montgomery(m);

    // precomputation
    var g = new Array(),
        n = 3,
        k1 = k - 1,
        km = (1 << k) - 1;
    g[1] = z.convert(this);
    if (k > 1) {
        var g2 = nbi();
        z.sqrTo(g[1], g2);
        while (n <= km) {
            g[n] = nbi();
            z.mulTo(g2, g[n - 2], g[n]);
            n += 2;
        }
    }

    var j = e.t - 1,
        w, is1 = true,
        r2 = nbi(),
        t;
    i = nbits(e[j]) - 1;
    while (j >= 0) {
        if (i >= k1) w = (e[j] >> (i - k1)) & km;
        else {
            w = (e[j] & ((1 << (i + 1)) - 1)) << (k1 - i);
            if (j > 0) w |= e[j - 1] >> (this.DB + i - k1);
        }

        n = k;
        while ((w & 1) == 0) {
            w >>= 1;
            --n;
        }
        if ((i -= n) < 0) {
            i += this.DB;
            --j;
        }
        if (is1) { // ret == 1, don't bother squaring or multiplying it
            g[w].copyTo(r);
            is1 = false;
        }
        else {
            while (n > 1) {
                z.sqrTo(r, r2);
                z.sqrTo(r2, r);
                n -= 2;
            }
            if (n > 0) z.sqrTo(r, r2);
            else {
                t = r;
                r = r2;
                r2 = t;
            }
            z.mulTo(r2, g[w], r);
        }

        while (j >= 0 && (e[j] & (1 << i)) == 0) {
            z.sqrTo(r, r2);
            t = r;
            r = r2;
            r2 = t;
            if (--i < 0) {
                i = this.DB - 1;
                --j;
            }
        }
    }
    return z.revert(r);
}

// (public) gcd(this,a) (HAC 14.54)

function bnGCD(a) {
    var x = (this.s < 0) ? this.negate() : this.clone();
    var y = (a.s < 0) ? a.negate() : a.clone();
    if (x.compareTo(y) < 0) {
        var t = x;
        x = y;
        y = t;
    }
    var i = x.getLowestSetBit(),
        g = y.getLowestSetBit();
    if (g < 0) return x;
    if (i < g) g = i;
    if (g > 0) {
        x.rShiftTo(g, x);
        y.rShiftTo(g, y);
    }
    while (x.signum() > 0) {
        if ((i = x.getLowestSetBit()) > 0) x.rShiftTo(i, x);
        if ((i = y.getLowestSetBit()) > 0) y.rShiftTo(i, y);
        if (x.compareTo(y) >= 0) {
            x.subTo(y, x);
            x.rShiftTo(1, x);
        }
        else {
            y.subTo(x, y);
            y.rShiftTo(1, y);
        }
    }
    if (g > 0) y.lShiftTo(g, y);
    return y;
}

// (protected) this % n, n < 2^26

function bnpModInt(n) {
    if (n <= 0) return 0;
    var d = this.DV % n,
        r = (this.s < 0) ? n - 1 : 0;
    if (this.t > 0) if (d == 0) r = this[0] % n;
    else for (var i = this.t - 1; i >= 0; --i) r = (d * r + this[i]) % n;
    return r;
}

// (public) 1/this % m (HAC 14.61)

function bnModInverse(m) {
    var ac = m.isEven();
    if ((this.isEven() && ac) || m.signum() == 0) return BigInteger.ZERO;
    var u = m.clone(),
        v = this.clone();
    var a = nbv(1),
        b = nbv(0),
        c = nbv(0),
        d = nbv(1);
    while (u.signum() != 0) {
        while (u.isEven()) {
            u.rShiftTo(1, u);
            if (ac) {
                if (!a.isEven() || !b.isEven()) {
                    a.addTo(this, a);
                    b.subTo(m, b);
                }
                a.rShiftTo(1, a);
            }
            else if (!b.isEven()) b.subTo(m, b);
            b.rShiftTo(1, b);
        }
        while (v.isEven()) {
            v.rShiftTo(1, v);
            if (ac) {
                if (!c.isEven() || !d.isEven()) {
                    c.addTo(this, c);
                    d.subTo(m, d);
                }
                c.rShiftTo(1, c);
            }
            else if (!d.isEven()) d.subTo(m, d);
            d.rShiftTo(1, d);
        }
        if (u.compareTo(v) >= 0) {
            u.subTo(v, u);
            if (ac) a.subTo(c, a);
            b.subTo(d, b);
        }
        else {
            v.subTo(u, v);
            if (ac) c.subTo(a, c);
            d.subTo(b, d);
        }
    }
    if (v.compareTo(BigInteger.ONE) != 0) return BigInteger.ZERO;
    if (d.compareTo(m) >= 0) return d.subtract(m);
    if (d.signum() < 0) d.addTo(m, d);
    else return d;
    if (d.signum() < 0) return d.add(m);
    else return d;
}

var lowprimes = [2, 3, 5, 7, 11, 13, 17, 19, 23, 29, 31, 37, 41, 43, 47, 53, 59, 61, 67, 71, 73, 79, 83, 89, 97, 101, 103, 107, 109, 113, 127, 131, 137, 139, 149, 151, 157, 163, 167, 173, 179, 181, 191, 193, 197, 199, 211, 223, 227, 229, 233, 239, 241, 251, 257, 263, 269, 271, 277, 281, 283, 293, 307, 311, 313, 317, 331, 337, 347, 349, 353, 359, 367, 373, 379, 383, 389, 397, 401, 409, 419, 421, 431, 433, 439, 443, 449, 457, 461, 463, 467, 479, 487, 491, 499, 503, 509, 521, 523, 541, 547, 557, 563, 569, 571, 577, 587, 593, 599, 601, 607, 613, 617, 619, 631, 641, 643, 647, 653, 659, 661, 673, 677, 683, 691, 701, 709, 719, 727, 733, 739, 743, 751, 757, 761, 769, 773, 787, 797, 809, 811, 821, 823, 827, 829, 839, 853, 857, 859, 863, 877, 881, 883, 887, 907, 911, 919, 929, 937, 941, 947, 953, 967, 971, 977, 983, 991, 997];
var lplim = (1 << 26) / lowprimes[lowprimes.length - 1];

// (public) test primality with certainty >= 1-.5^t

function bnIsProbablePrime(t) {
    var i, x = this.abs();
    if (x.t == 1 && x[0] <= lowprimes[lowprimes.length - 1]) {
        for (i = 0; i < lowprimes.length; ++i)
            if (x[0] == lowprimes[i]) return true;
        return false;
    }
    if (x.isEven()) return false;
    i = 1;
    while (i < lowprimes.length) {
        var m = lowprimes[i],
            j = i + 1;
        while (j < lowprimes.length && m < lplim) m *= lowprimes[j++];
        m = x.modInt(m);
        while (i < j) if (m % lowprimes[i++] == 0) return false;
    }
    return x.millerRabin(t);
}

// (protected) true if probably prime (HAC 4.24, Miller-Rabin)

function bnpMillerRabin(t) {
    var n1 = this.subtract(BigInteger.ONE);
    var k = n1.getLowestSetBit();
    if (k <= 0) return false;
    var r = n1.shiftRight(k);
    t = (t + 1) >> 1;
    if (t > lowprimes.length) t = lowprimes.length;
    var a = nbi();
    for (var i = 0; i < t; ++i) {
        //Pick bases at random, instead of starting at 2
        a.fromInt(lowprimes[Math.floor(Math.random() * lowprimes.length)]);
        var y = a.modPow(r, this);
        if (y.compareTo(BigInteger.ONE) != 0 && y.compareTo(n1) != 0) {
            var j = 1;
            while (j++ < k && y.compareTo(n1) != 0) {
                y = y.modPowInt(2, this);
                if (y.compareTo(BigInteger.ONE) == 0) return false;
            }
            if (y.compareTo(n1) != 0) return false;
        }
    }
    return true;
}

// protected
BigInteger.prototype.chunkSize = bnpChunkSize;
BigInteger.prototype.toRadix = bnpToRadix;
BigInteger.prototype.fromRadix = bnpFromRadix;
BigInteger.prototype.fromNumber = bnpFromNumber;
BigInteger.prototype.bitwiseTo = bnpBitwiseTo;
BigInteger.prototype.changeBit = bnpChangeBit;
BigInteger.prototype.addTo = bnpAddTo;
BigInteger.prototype.dMultiply = bnpDMultiply;
BigInteger.prototype.dAddOffset = bnpDAddOffset;
BigInteger.prototype.multiplyLowerTo = bnpMultiplyLowerTo;
BigInteger.prototype.multiplyUpperTo = bnpMultiplyUpperTo;
BigInteger.prototype.modInt = bnpModInt;
BigInteger.prototype.millerRabin = bnpMillerRabin;

// public
BigInteger.prototype.clone = bnClone;
BigInteger.prototype.intValue = bnIntValue;
BigInteger.prototype.byteValue = bnByteValue;
BigInteger.prototype.shortValue = bnShortValue;
BigInteger.prototype.signum = bnSigNum;
BigInteger.prototype.toByteArray = bnToByteArray;
BigInteger.prototype.equals = bnEquals;
BigInteger.prototype.min = bnMin;
BigInteger.prototype.max = bnMax;
BigInteger.prototype.and = bnAnd;
BigInteger.prototype.or = bnOr;
BigInteger.prototype.xor = bnXor;
BigInteger.prototype.andNot = bnAndNot;
BigInteger.prototype.not = bnNot;
BigInteger.prototype.shiftLeft = bnShiftLeft;
BigInteger.prototype.shiftRight = bnShiftRight;
BigInteger.prototype.getLowestSetBit = bnGetLowestSetBit;
BigInteger.prototype.bitCount = bnBitCount;
BigInteger.prototype.testBit = bnTestBit;
BigInteger.prototype.setBit = bnSetBit;
BigInteger.prototype.clearBit = bnClearBit;
BigInteger.prototype.flipBit = bnFlipBit;
BigInteger.prototype.add = bnAdd;
BigInteger.prototype.subtract = bnSubtract;
BigInteger.prototype.multiply = bnMultiply;
BigInteger.prototype.divide = bnDivide;
BigInteger.prototype.remainder = bnRemainder;
BigInteger.prototype.divideAndRemainder = bnDivideAndRemainder;
BigInteger.prototype.modPow = bnModPow;
BigInteger.prototype.modInverse = bnModInverse;
BigInteger.prototype.pow = bnPow;
BigInteger.prototype.gcd = bnGCD;
BigInteger.prototype.isProbablePrime = bnIsProbablePrime;

// JSBN-specific extension
BigInteger.prototype.square = bnSquare;

/*
------------------------------------------------------------------------------------------------------------------------
prng4.js
------------------------------------------------------------------------------------------------------------------------
*/
// prng4.js - uses Arcfour as a PRNG

function Arcfour() {
    this.i = 0;
    this.j = 0;
    this.S = new Array();
}

// Initialize arcfour context from key, an array of ints, each from [0..255]
function ARC4init(key) {
    var i, j, t;
    for (i = 0; i < 256; ++i)
        this.S[i] = i;
    j = 0;
    for (i = 0; i < 256; ++i) {
        j = (j + this.S[i] + key[i % key.length]) & 255;
        t = this.S[i];
        this.S[i] = this.S[j];
        this.S[j] = t;
    }
    this.i = 0;
    this.j = 0;
}

function ARC4next() {
    var t;
    this.i = (this.i + 1) & 255;
    this.j = (this.j + this.S[this.i]) & 255;
    t = this.S[this.i];
    this.S[this.i] = this.S[this.j];
    this.S[this.j] = t;
    return this.S[(t + this.S[this.i]) & 255];
}

Arcfour.prototype.init = ARC4init;
Arcfour.prototype.next = ARC4next;

// Plug in your RNG constructor here
function prng_newstate() {
    return new Arcfour();
}

// Pool size must be a multiple of 4 and greater than 32.
// An array of bytes the size of the pool will be passed to init()
var rng_psize = 256;

/*
------------------------------------------------------------------------------------------------------------------------
rng.js
------------------------------------------------------------------------------------------------------------------------
*/
// Random number generator - requires a PRNG backend, e.g. prng4.js

// For best results, put code like
// <body onClick='rng_seed_time();' onKeyPress='rng_seed_time();'>
// in your main HTML document.

var rng_state;
var rng_pool;
var rng_pptr;

// Mix in a 32-bit integer into the pool
function rng_seed_int(x) {
    rng_pool[rng_pptr++] ^= x & 255;
    rng_pool[rng_pptr++] ^= (x >> 8) & 255;
    rng_pool[rng_pptr++] ^= (x >> 16) & 255;
    rng_pool[rng_pptr++] ^= (x >> 24) & 255;
    if (rng_pptr >= rng_psize) rng_pptr -= rng_psize;
}

// Mix in the current time (w/milliseconds) into the pool
function rng_seed_time() {
    rng_seed_int(new Date().getTime());
}

// Initialize the pool with junk if needed.
if (rng_pool == null) {
    rng_pool = new Array();
    rng_pptr = 0;
    var t;
    if (window.crypto && window.crypto.getRandomValues) {
        // Use webcrypto if available
        var ua = new Uint8Array(32);
        window.crypto.getRandomValues(ua);
        for (t = 0; t < 32; ++t)
            rng_pool[rng_pptr++] = ua[t];
    }
    if (navigator.appName == "Netscape" && navigator.appVersion < "5" && window.crypto) {
        // Extract entropy (256 bits) from NS4 RNG if available
        var z = window.crypto.random(32);
        for (t = 0; t < z.length; ++t)
            rng_pool[rng_pptr++] = z.charCodeAt(t) & 255;
    }
    while (rng_pptr < rng_psize) {  // extract some randomness from Math.random()
        t = Math.floor(65536 * Math.random());
        rng_pool[rng_pptr++] = t >>> 8;
        rng_pool[rng_pptr++] = t & 255;
    }
    rng_pptr = 0;
    rng_seed_time();
    //rng_seed_int(window.screenX);
    //rng_seed_int(window.screenY);
}

function rng_get_byte() {
    if (rng_state == null) {
        rng_seed_time();
        rng_state = prng_newstate();
        rng_state.init(rng_pool);
        for (rng_pptr = 0; rng_pptr < rng_pool.length; ++rng_pptr)
            rng_pool[rng_pptr] = 0;
        rng_pptr = 0;
        //rng_pool = null;
    }
    // TODO: allow reseeding after first request
    return rng_state.next();
}

function rng_get_bytes(ba) {
    var i;
    for (i = 0; i < ba.length; ++i) ba[i] = rng_get_byte();
}

function SecureRandom() { }

SecureRandom.prototype.nextBytes = rng_get_bytes;

/*
Modifications are done by vinod 01 MAY 2018
https://stackoverflow.com/questions/13472782/openssl-decryption-in-javascript?lq=1
https://pastebin.com/GfhuDwj5
*/

var RSAPublicKey = function ($modulus, $encryptionExponent) {
    this.modulus = new BigInteger(Hex.encode($modulus), 16);
    this.encryptionExponent = new BigInteger(Hex.encode($encryptionExponent), 16);
}

var UTF8 = {
    encode: function ($input) {
        $input = $input.replace(/\r\n/g, "\n");
        var $output = "";
        for (var $n = 0; $n < $input.length; $n++) {
            var $c = $input.charCodeAt($n);
            if ($c < 128) {
                $output += String.fromCharCode($c);
            } else if (($c > 127) && ($c < 2048)) {
                $output += String.fromCharCode(($c >> 6) | 192);
                $output += String.fromCharCode(($c & 63) | 128);
            } else {
                $output += String.fromCharCode(($c >> 12) | 224);
                $output += String.fromCharCode((($c >> 6) & 63) | 128);
                $output += String.fromCharCode(($c & 63) | 128);
            }
        }
        return $output;
    },
    decode: function ($input) {
        var $output = "";
        var $i = 0;
        var $c = $c1 = $c2 = 0;
        while ($i < $input.length) {
            $c = $input.charCodeAt($i);
            if ($c < 128) {
                $output += String.fromCharCode($c);
                $i++;
            } else if (($c > 191) && ($c < 224)) {
                $c2 = $input.charCodeAt($i + 1);
                $output += String.fromCharCode((($c & 31) << 6) | ($c2 & 63));
                $i += 2;
            } else {
                $c2 = $input.charCodeAt($i + 1);
                $c3 = $input.charCodeAt($i + 2);
                $output += String.fromCharCode((($c & 15) << 12) | (($c2 & 63) << 6) | ($c3 & 63));
                $i += 3;
            }
        }
        return $output;
    }
};

var Base64 = {
    base64: "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",
    encode: function ($input) {
        if (!$input) {
            return false;
        }
        //$input = UTF8.encode($input);
        var $output = "";
        var $chr1, $chr2, $chr3;
        var $enc1, $enc2, $enc3, $enc4;
        var $i = 0;
        do {
            $chr1 = $input.charCodeAt($i++);
            $chr2 = $input.charCodeAt($i++);
            $chr3 = $input.charCodeAt($i++);
            $enc1 = $chr1 >> 2;
            $enc2 = (($chr1 & 3) << 4) | ($chr2 >> 4);
            $enc3 = (($chr2 & 15) << 2) | ($chr3 >> 6);
            $enc4 = $chr3 & 63;
            if (isNaN($chr2)) $enc3 = $enc4 = 64;
            else if (isNaN($chr3)) $enc4 = 64;
            $output += this.base64.charAt($enc1) + this.base64.charAt($enc2) + this.base64.charAt($enc3) + this.base64.charAt($enc4);
        } while ($i < $input.length);
        return $output;
    },
    decode: function ($input) {
        if (!$input) return false;
        $input = $input.replace(/[^A-Za-z0-9\+\/\=]/g, "");
        var $output = "";
        var $enc1, $enc2, $enc3, $enc4;
        var $i = 0;
        do {
            $enc1 = this.base64.indexOf($input.charAt($i++));
            $enc2 = this.base64.indexOf($input.charAt($i++));
            $enc3 = this.base64.indexOf($input.charAt($i++));
            $enc4 = this.base64.indexOf($input.charAt($i++));
            $output += String.fromCharCode(($enc1 << 2) | ($enc2 >> 4));
            if ($enc3 != 64) $output += String.fromCharCode((($enc2 & 15) << 4) | ($enc3 >> 2));
            if ($enc4 != 64) $output += String.fromCharCode((($enc3 & 3) << 6) | $enc4);
        } while ($i < $input.length);
        return $output; //UTF8.decode($output);
    }
};

var Hex = {
    hex: "0123456789abcdef",
    encode: function ($input) {
        if (!$input) return false;
        var $output = "";
        var $k;
        var $i = 0;
        do {
            $k = $input.charCodeAt($i++);
            $output += this.hex.charAt(($k >> 4) & 0xf) + this.hex.charAt($k & 0xf);
        } while ($i < $input.length);
        return $output;
    },
    decode: function ($input) {
        if (!$input) return false;
        $input = $input.replace(/[^0-9abcdef]/g, "");
        var $output = "";
        var $i = 0;
        do {
            $output += String.fromCharCode(((this.hex.indexOf($input.charAt($i++)) << 4) & 0xf0) | (this.hex.indexOf($input.charAt($i++)) & 0xf));
        } while ($i < $input.length);
        return $output;
    }
};

var ASN1Data = function ($data) {
    this.error = false;
    this.parse = function ($data) {
        if (!$data) {
            this.error = true;
            return null;
        }
        var $result = [];
        while ($data.length > 0) {
            // get the tag
            var $tag = $data.charCodeAt(0);
            $data = $data.substr(1);
            // get length
            var $length = 0;
            // ignore any null tag
            if (($tag & 31) == 0x5) $data = $data.substr(1);
            else {
                if ($data.charCodeAt(0) & 128) {
                    var $lengthSize = $data.charCodeAt(0) & 127;
                    $data = $data.substr(1);
                    if ($lengthSize > 0) $length = $data.charCodeAt(0);
                    if ($lengthSize > 1) $length = (($length << 8) | $data.charCodeAt(1));
                    if ($lengthSize > 2) {
                        this.error = true;
                        return null;
                    }
                    $data = $data.substr($lengthSize);
                } else {
                    $length = $data.charCodeAt(0);
                    $data = $data.substr(1);
                }
            }
            // get value
            var $value = "";
            if ($length) {
                if ($length > $data.length) {
                    this.error = true;
                    return null;
                }
                $value = $data.substr(0, $length);
                $data = $data.substr($length);
            }
            if ($tag & 32)
                $result.push(this.parse($value)); // sequence
            else
                $result.push(this.value(($tag & 128) ? 4 : ($tag & 31), $value));
        }
        return $result;
    };
    this.value = function ($tag, $data) {
        if ($tag == 1)
            return $data ? true : false;
        else if ($tag == 2) //integer
            return $data;
        else if ($tag == 3) //bit string
            return this.parse($data.substr(1));
        else if ($tag == 5) //null
            return null;
        else if ($tag == 6) { //ID
            var $res = [];
            var $d0 = $data.charCodeAt(0);
            $res.push(Math.floor($d0 / 40));
            $res.push($d0 - $res[0] * 40);
            var $stack = [];
            var $powNum = 0;
            var $i;
            for ($i = 1; $i < $data.length; $i++) {
                var $token = $data.charCodeAt($i);
                $stack.push($token & 127);
                if ($token & 128)
                    $powNum++;
                else {
                    var $j;
                    var $sum = 0;
                    for ($j = 0; $j < $stack.length; $j++)
                        $sum += $stack[$j] * Math.pow(128, $powNum--);
                    $res.push($sum);
                    $powNum = 0;
                    $stack = [];
                }
            }
            return $res.join(".");
        }
        return null;
    }
    this.data = this.parse($data);
};

/* var RSA = {
    getPublicKey: function($pem) {
        if($pem.length<50) return false;
        if($pem.substr(0,26)!="-----BEGIN PUBLIC KEY-----") return false;
        $pem = $pem.substr(26);
        if($pem.substr($pem.length-24)!="-----END PUBLIC KEY-----") return false;
        $pem = $pem.substr(0,$pem.length-24);
        $pem = new ASN1Data(Base64.decode($pem));
        if($pem.error) return false;
        $pem = $pem.data;
        if($pem[0][0][0]=="1.2.840.113549.1.1.1")
            return new RSAPublicKey($pem[0][1][0][0], $pem[0][1][0][1]);
        return false;
    },
    encrypt: function($data, $pubkey) {
        if (!$pubkey) return false;
        var bytes = ($pubkey.modulus.bitLength()+7)>>3;
        $data = this.pkcs1pad2($data,bytes);
        if(!$data) return false;
        $data = $data.modPowInt($pubkey.encryptionExponent, $pubkey.modulus);
        if(!$data) return false;
        $data = $data.toString(16);
        while ($data.length < bytes*2)
            $data = '0' + $data;
        return Base64.encode(Hex.decode($data));
    },
    pkcs1pad2: function(s, n) { // $data, $keysize
 
        if(n < s.length + 11) { // TODO: fix for utf-8
            console.error("Too long for RSA");
            return null;
          }
          
          var ba = new Array();
          var i = s.length - 1;
          while(i >= 0 && n > 0) {
            var c = s.charCodeAt(i--);
            if(c < 128) { // encode using utf-8
              ba[--n] = c;
            }
            else if((c > 127) && (c < 2048)) {
              ba[--n] = (c & 63) | 128;
              ba[--n] = (c >> 6) | 192;
            }
            else {
              ba[--n] = (c & 63) | 128;
              ba[--n] = ((c >> 6) & 63) | 128;
              ba[--n] = (c >> 12) | 224;
            }
          }
          ba[--n] = 0;
          var rng = new SecureRandom();
          var x = new Array();
          while(n > 2) { // random non-zero pad
            x[0] = 0;
            while(x[0] == 0) rng.nextBytes(x);
            ba[--n] = x[0];
          }
          ba[--n] = 2;
          ba[--n] = 0;
          return new BigInteger(ba);
    }
}
 */

var RSA = {
    getPublicKey: function ($pem) {
        if ($pem.length < 50)
            return false;
        if ($pem.substr(0, 26) != "-----BEGIN PUBLIC KEY-----")
            return false;
        $pem = $pem.substr(26);
        if ($pem.substr($pem.length - 24) != "-----END PUBLIC KEY-----")
            return false;
        $pem = $pem.substr(0, $pem.length - 24);
        $pem = new ASN1Data(Base64.decode($pem));
        if ($pem.error)
            return false;
        $pem = $pem.data;
        if ($pem[0][0][0] == "1.2.840.113549.1.1.1")
            return new RSAPublicKey($pem[0][1][0][0], $pem[0][1][0][1]);
        return false;
    },
    encrypt: function ($data, $pubkey) {
        if (!$pubkey)
            return false;
        var bytes = ($pubkey.modulus.bitLength() + 7) >> 3;
        $data = this.pkcs1pad2($data, bytes);
        if (!$data)
            return false;
        $data = $data.modPowInt($pubkey.encryptionExponent, $pubkey.modulus);
        if (!$data)
            return false;
        $data = $data.toString(16);
        while ($data.length < bytes * 2)
            $data = '0' + $data;
        return Base64.encode(Hex.decode($data));
    },
    decrypt: function ($text, $pubkey) {
        if (!$pubkey)
            return false;
        $text = Hex.encode(Base64.decode($text)).toString(16);
        while ($text[0] == '0')
            $text = $text.replace('0', '');
        $text = new BigInteger($text, 16);
        $text = $text.modPowInt($pubkey.encryptionExponent, $pubkey.modulus);
        if (!$text)
            return false;
        var bytes = ($pubkey.modulus.bitLength() + 7) >> 3;
        $text = this.pkcs1unpad2($text, bytes);

        return $text;
    },
    pkcs1pad2: function pkcs1pad2(s, n) {
        if (n < s.length + 11) { // TODO: fix for utf-8
            // "Too long for RSA";
            return null;
        }
        var ba = new Array();
        var i = s.length - 1;
        while (i >= 0 && n > 0) {
            var c = s.charCodeAt(i--);
            if (c < 128) { // encode using utf-8
                ba[--n] = c;
            }
            else if ((c > 127) && (c < 2048)) {
                ba[--n] = (c & 63) | 128;
                ba[--n] = (c >> 6) | 192;
            }
            else {
                ba[--n] = (c & 63) | 128;
                ba[--n] = ((c >> 6) & 63) | 128;
                ba[--n] = (c >> 12) | 224;
            }
        }
        ba[--n] = 0;
        var rng = new SecureRandom();
        var x = new Array();
        while (n > 2) { // random non-zero pad
            x[0] = 0;
            while (x[0] == 0)
                rng.nextBytes(x);
            ba[--n] = x[0];
        }
        ba[--n] = 2;
        ba[--n] = 0;
        return new BigInteger(ba);
    },
    pkcs1unpad2: function pkcs1unpad2(d, n) {
        var b = d.toByteArray();
        var i = 0;
        while (i < b.length && b[i] == 0)
            ++i;
        /*if (b.length - i != n - 1 || b[i] != 2)
         return null;*/
        ++i;
        while (b[i] != 0)
            if (++i >= b.length)
                return null;
        var ret = "";
        while (++i < b.length) {
            var c = b[i] & 255;
            if (c < 128) { // utf-8 decode
                ret += String.fromCharCode(c);
            }
            else if ((c > 191) && (c < 224)) {
                ret += String.fromCharCode(((c & 31) << 6) | (b[i + 1] & 63));
                ++i;
            }
            else {
                ret += String.fromCharCode(((c & 15) << 12) | ((b[i + 1] & 63) << 6) | (b[i + 2] & 63));
                i += 2;
            }
        }
        return ret;
    }
};
(function (window, undefined) {

    'use strict';
    /**
     * __InputSecurity : 28 NOV 2019
     * LivelyWorks
     *
     *-------------------------------------------------------- */

    window.__InputSecurity = {
        /**
         *
         * Decrypt string using RSA with Public Key
         *
         * @return object
         *-------------------------------------------------------- */
        rsaDecrypt: function (encryptedString) {
            return RSA.decrypt(encryptedString, __InputSecurity.getPublicRSA());
        },

        /**
         *
         * Encrypt string using RSA with Public Key
         *
         * @return object
         *-------------------------------------------------------- */
        rsaEncrypt: function (plainString) {
            return RSA.encrypt(plainString, __InputSecurity.getPublicRSA());
        },

        /**
         *
         * get security token
         *
         * @return string
         *-------------------------------------------------------- */
        getPublicRSA: function () {
            return RSA.getPublicKey(
                window.__pbkey ? window.__pbkey : "-----BEGIN PUBLIC KEY-----MFwwDQYJKoZIhvcNAQEBBQADSwAwSAJBAPJwwNa//eaQYxkNsAODohg38azVtalEh7Lw4wxlBrbDONgYaebgscpjPRloeL0kj4aLI462lcQGVAxhyh8JijsCAwEAAQ==-----END PUBLIC KEY-----"
            );
        },

        /**
         * Process encrypted data
         *
         * @return void
         *-------------------------------------------------------- */

        processSecuredData: function (responseData) {
            if (!responseData || !responseData["__maskedData"]) {
                return false;
            } else {
                var splitedValues = responseData["__maskedData"].split("__==__");
                var splitedValueString = "";
                for (var i = 0; i < splitedValues.length; i++) {
                    if (splitedValues[i]) {
                        splitedValueString += __InputSecurity.rsaDecrypt(
                            splitedValues[i]
                        );
                    }
                }
                return JSON.parse(splitedValueString);
            }
        },

        /**
         * process response data whatever it is secured or not returns decrypted data
         *
         * @return void
         *-------------------------------------------------------- */

        processResponseData: function (responseData) {
            var processedData = __InputSecurity.processSecuredData(responseData);
            if (processedData == false) {
                return responseData;
            } else {
                return processedData;
            }
        },

        processFormFields: function (dataObj, options) {
            if (dataObj && !_.isEmpty(dataObj)) {
                var newDataObj = {};
                if (!options) {
                    options = {};
                }
                options.secured = true;
                if (options && options.secured == true) {
                    _.forEach(dataObj, function (value, key) {
                        var encryptedKey = __InputSecurity.rsaEncrypt(
                            key
                        );
                        if (
                            (!options.unsecuredFields ||
                                _.includes(options.unsecuredFields, key) ===
                                false) &&
                            (_.isArray(value) === true ||
                                _.isObject(value) === true)
                            && (_.isEmpty(value) === true)
                        ) {
                            newDataObj[encryptedKey] = value;
                        } else if (
                            (!options.unsecuredFields ||
                                _.includes(options.unsecuredFields, key) ===
                                false) &&
                            (_.isArray(value) === true ||
                                _.isObject(value) === true )
                            && (_.isEmpty(value) === false)
                        ) {
                            newDataObj[encryptedKey] = __InputSecurity.processFormFields(
                                value
                            );
                        } else if (
                            (!options.unsecuredFields ||
                                _.includes(options.unsecuredFields, key) ===
                                false) &&
                            _.isArray(value) !== true &&
                            _.isObject(value) !== true
                        ) {
                            if (value || value == false) {
                                if (_.isBoolean(value) || _.isNumber(value)) {
                                    value = String(value);
                                }

                                var securedValue = __InputSecurity.rsaEncrypt(
                                    value
                                );
                                // if cannot be encrypt may long a long string and needs to be concat.
                                if (securedValue == false) {
                                    var splitedValues = value.match(/.{1,30}/g),
                                        splitedValueString = "";

                                    for (var i = 0; i < splitedValues.length; i++) {
                                        var securedSplitedValue = __InputSecurity.rsaEncrypt(
                                            splitedValues[i]
                                        );

                                        if (securedSplitedValue == false) {
                                            throw "Encryption Failed for { " +
                                            key +
                                            " } VALUE due to length";

                                            splitedValueString = false;
                                            break;
                                        } else {
                                            splitedValueString =
                                                splitedValueString +
                                                securedSplitedValue +
                                                "__==__";
                                        }
                                    }

                                    securedValue = splitedValueString;
                                }

                                // var securedKey = __InputSecurity.rsaEncrypt(key);
                                if (encryptedKey == false) {
                                    throw "Encryption Failed for { " +
                                    encryptedKey +
                                    " } KEY due to length";
                                }

                                newDataObj[encryptedKey] = securedValue;
                            }
                        } else {
                            newDataObj[key] = value;
                        }
                    });
                } else {
                    newDataObj = dataObj;
                }
            }

            return newDataObj;
        }
    };
})(window);
(function (window, undefined) {

    'use strict';

    _.templateSettings.variable = "__tData";

    window.__globals = {
        translate_strings: {},
        default_show_message: false
    };

    if (window.appConfig['locale']) {
        var localeNumberFormat = new Intl.NumberFormat(window.appConfig.locale);
    } else {
        var localeNumberFormat = null;
    }

    /**
      * Common Functions : 08 JAN 2020
      * LivelyWorks
      *
      *-------------------------------------------------------- */
    window.__Utils = {
        log: function (text, textStyle) {

            if (window.appConfig && window.appConfig.debug) {

                var consoleTextStyle = '',
                    prependForStyle = '';

                if (textStyle && _.isString(text)) {
                    consoleTextStyle = textStyle;
                    prependForStyle = '%c ';

                    console.log(prependForStyle + text, consoleTextStyle);
                } else {
                    console.log(text);
                }
                consoleTextStyle = prependForStyle = null;
            }
        },

        syntaxHighlight: function (json) {
            json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
                var cls = 'color: darkorange;'; /*number*/
                if (/^"/.test(match)) {
                    if (/:$/.test(match)) {
                        cls = 'color: red;'; /*key*/
                    } else {
                        cls = 'color: green;'; /*string*/
                    }
                } else if (/true|false/.test(match)) {
                    cls = 'color: blue;'; /*boolean*/
                } else if (/null/.test(match)) {
                    cls = 'color: magenta;'; /*null*/
                }
                return '<span style="' + cls + '">' + match + '</span>';
            });
        },

        displayInTabWindow: function (text) {

            if (window.appConfig && window.appConfig.debug) {
                if (text) {
                    var textToPrint = '';
                    if (_.isObject(text)) {
                        if (_.has(text, 'data')) {
                            textToPrint = '<pre style="font-size:14px; outline: 1px solid #ccc; padding: 10px; margin: 0px;"><strong>URL: </strong>' + (text.config ? text.config.url : '') + ' <strong><br>Method: </strong>' + (text.config ? text.config.method : '') + ' <strong><br>statusText: </strong>' + text.statusText + ' (' + text.status + ') <strong style="color:red"><br>Error Message: ' + text.data.message + '</strong></pre>';
                        }
                        textToPrint += '<pre style="outline: 1px solid #ccc; padding: 5px; margin: 0px;">' + __Utils.syntaxHighlight(JSON.stringify(text, null, 4)) + '</pre>';
                    } else {
                        textToPrint = text;
                    }
                    var dynamicTabWindow = window.open('', '_blank');
                    dynamicTabWindow.document.write(textToPrint);
                    dynamicTabWindow.document.close(); // necessary for IE >= 10
                    dynamicTabWindow.focus(); // necessary for IE >= 10
                } else {
                    console.log("__Utils: Text not found for window.")
                }
                text = textToPrint = null;
            }
        },

        openEmailDebugView: function (url) {
            if (window.appConfig && window.appConfig.debug) {
                window.open(url, "__emailDebugView");
                __Utils.info("Request Sent to open Email Debug View.");
            }
        },

        error: function (text) {
            if (window.appConfig && window.appConfig.debug) {
                console.error(text);
            }
        },

        info: function (text) {
            if (window.appConfig && window.appConfig.debug) {
                console.info(text);
            }
        },

        warn: function (text) {
            if (window.appConfig && window.appConfig.debug) {
                console.warn(text);
            }
        },

        throwError: function (text) {
            if (window.appConfig && window.appConfig.debug) {
                throw new Error(text);
            }
        },

        jsdd: function (response) {
            if (window.appConfig && window.appConfig.debug) {
                if (response.__dd && response.__pr) {
                    if (!response.__prExecuted) {
                        var prCount = 1;
                        _.forEach(response.__pr, function (__prValue) {
                            var debugBacktrace = '';
                            console.log('%c Server __pr ' + prCount + " --------------------------------------------------", 'color:#f0ad4e');
                            _.forEach(__prValue, function (value, key) {
                                if (key !== 'debug_backtrace') {
                                    console.log(value);
                                } else {
                                    debugBacktrace = value;
                                }
                            });
                            console.log('%c Reference  --------------------------------------------------', 'color:#f0ad4e');
                            console.log(debugBacktrace);
                            prCount++;
                        });
                        response.__prExecuted = true;
                        console.log("%c ------------------------------------------------------------ __pr end", 'color: #f0ad4e');
                    }
                }
                if (response.__dd && response.__clog) {
                    if (!response.__clogExecuted) {
                        __Utils.clog(response);
                        response.__clogExecuted = true;
                    }
                }

                if (response.__dd && response.__dd === '__dd') {
                    if (!response.__ddExecuted) {
                        console.log('%c Server __dd  --------------------------------------------------', 'color:#ff0000');
                        var ddCount = 1;
                        _.forEach(response.data, function (value, key) {
                            if (key !== 'debug_backtrace') {
                                console.log(value);
                                ddCount++;
                            } else {
                                console.log('%c Reference  --------------------------------------------------', 'color:#ff0000');
                                console.log(value);
                            }
                        });
                        response.__ddExecuted = true;
                    }
                    console.log("%c ------------------------------------------------------------ __dd end", 'color: #ff0000');
                    throw '------------------------------------------------------------ __dd end.';
                }
            }
        },
        /**
         * Console the items requested from __clog Laraware helper function
         *
         *-------------------------------------------------------- */
        clog: function (clogData) {
            if (!__globals) {
                var __globals = {
                    __clogCount: 0
                }
            }
            var clCount = 1,
                clogType = clogData.__clogType ? clogData.__clogType : '';
            _.forEach(clogData.__clog, function (__clogValue) {
                _.forEach(__clogValue, function (value) {
                    console.log('%c __clog ' + clogType + ' ' + clCount + " --------------------------------------------------", 'color: #bada55');
                    console.log('%c ' + value, 'color: #9c9c9c');
                    clCount++;
                    __globals.__clogCount++;
                });
            });

            console.log("%c ------------------------------------------------------------ __clog " + clogType + " items end." + ' TotalCount: ' + __globals.__clogCount, 'color: #bada55');
        },
        /**
         * detect IE
         * returns version of IE or false, if browser is not Internet Explorer
         *
         */
        detectIE: function () {

            var ua = window.navigator.userAgent;

            // Test values; Uncomment to check result …

            // IE 10
            // ua = 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.2; Trident/6.0)';

            // IE 11
            // ua = 'Mozilla/5.0 (Windows NT 6.3; Trident/7.0; rv:11.0) like Gecko';

            // Edge 12 (Spartan)
            // ua = 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.71 Safari/537.36 Edge/12.0';

            // Edge 13
            // ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2486.0 Safari/537.36 Edge/13.10586';

            var msie = ua.indexOf('MSIE ');
            if (msie > 0) {
                // IE 10 or older => return version number
                return parseInt(ua.substring(msie + 5, ua.indexOf('.', msie)), 10);
            }

            var trident = ua.indexOf('Trident/');
            if (trident > 0) {
                // IE 11 => return version number
                var rv = ua.indexOf('rv:');
                return parseInt(ua.substring(rv + 3, ua.indexOf('.', rv)), 10);
            }

            var edge = ua.indexOf('Edge/');
            if (edge > 0) {
                // Edge (IE 12+) => return version number
                return parseInt(ua.substring(edge + 5, ua.indexOf('.', edge)), 10);
            }

            // other browser
            return false;
        },

        time: function (text) {
            if (window.appConfig && window.appConfig.debug && (__Utils.detectIE() >= 11 || __Utils.detectIE() == false)) {
                console.time(text);
                text = null;
            }
        },

        timeEnd: function (text) {
            if (window.appConfig && window.appConfig.debug && (__Utils.detectIE() >= 11 || __Utils.detectIE() == false)) {
                console.timeEnd(text);
                text = null;
            }
        },
        /**
        * Templating Modal
        * @param templateId string template identifer
        * @param responseCallback callback function should return values required for template
        * return void
        *-------------------------------------------------------- */
        modalTemplatize: function (templateId, responseCallback, closeCallback) {
            var $templateStructure = $(templateId),
                _thisDeferred = $.Deferred(),
                modalEvent = $templateStructure.data('modalEvent'),
                modalCloseEvent = $templateStructure.data('modalCloseEvent'),
                modalId = $templateStructure.data('modalId'),
                compiledTemplate = _.template($templateStructure.html()),
                replaceId = $templateStructure.data('replaceTarget');

            var callbackResponse = {};
            if (responseCallback) {
                $(modalId).on((modalEvent ? modalEvent : 'show') + '.bs.modal', function (e) {
                    var $templateStructure = $(templateId),
                        compiledTemplate = _.template($templateStructure.html()),
                        replaceId = $templateStructure.data('replaceTarget');
                    if (typeof responseCallback === 'function') {
                        callbackResponse = responseCallback(e, $(e.relatedTarget).data());
                        _thisDeferred.resolve(callbackResponse);
                    } else {
                        __Utils.error('responseCallback should be function');
                    }
                    //append rather than replace!
                    $(replaceId ? replaceId : 'modal-body').html(compiledTemplate(callbackResponse));
                });
            } else {
                _thisDeferred.resolve(callbackResponse);
            }

            if (closeCallback) {
                $(modalId).on((modalCloseEvent ? modalCloseEvent : 'hidden') + '.bs.modal', function (hiddenEvent) {
                    if (typeof closeCallback === 'function') {
                        closeCallback(hiddenEvent, callbackResponse);
                    } else {
                        __Utils.error('closeCallback should be function');
                    }
                });
            }
            $templateStructure = modalCloseEvent = modalId = compiledTemplate = replaceId = callbackResponse = null;
            return _thisDeferred.promise();
        },
        /**
         * Convert the query string to object
         */
        queryConvertToObject: function (queryStr) {
            if (_.isString(queryStr)) {
                var queryArr = (queryStr).replace('?', '&').split('&'),
                    queryParams = {};
                for (var q = 0, qArrLength = queryArr.length; q < qArrLength; q++) {
                    var qArr = queryArr[q].split('=');
                    queryParams[decodeURIComponent(qArr[0])] = decodeURIComponent(qArr[1]);
                }
                queryStr = queryArr = null;
                return queryParams;
            } else {
                return queryStr;
            }
        },

        viewReload: function () {
            location.reload();
        },

        /**
         * Underscore template compilation utility
         *
         * @param string {templateName} - html template identifier including (# for id or . for class)
         * @param object {dataObj}
         *
         * @return formatted html
         *-------------------------------------------------------- */

        template: function (templateName, dataObj) {
            var $templateHtml = $("script" + templateName).html();
            if ($templateHtml) {
                var _template = _.template($templateHtml);
                $templateHtml = templateName = null;
                return _template(dataObj);
            } else {
                return dataObj;
            }
        },

        /**
         * Get URL string based on Laravel Routes.
         *
         * @param  string/object route
         * @param  params object
         *
         * @return string
         *-------------------------------------------------------- */

        apiURL: function (route, params) {
            // Check if route is string
            if (_.isString(route)) {
                if (!_.isEmpty(params) && _.isObject(params)) {
                    _.forEach(params, function (value, key) {
                        route = route.replace(key, value);
                    });
                }
            } else {
                __Utils.error("__Utils:: Invalid API url");
            }
            params = null;
            return route;
        },

        /**
         * Get translate
         *
         * @param  string stringKey
         *
         * @return string
         *-------------------------------------------------------- */

        getTranslation: function (stringKey, fallBackString) {
            // Check if translation available
            if (__globals.translate_strings[stringKey]) {
                return __globals.translate_strings[stringKey]
            } else {
                return fallBackString ? fallBackString : stringKey;
            }
        },

        /**
         * Get translate
         *
         * @param  string stringKey
         *
         * @return string
         *-------------------------------------------------------- */
        setTranslation: function (stringKey, stringTranslation) {
            if (_.isObject(stringKey)) {
                __globals.translate_strings = _.assign(__globals.translate_strings, stringKey);
                stringKey = stringTranslation = null;
                return true;
            } else if (_.isString(stringKey) && stringTranslation) {
                __globals.translate_strings[stringKey] = stringTranslation;
                stringKey = stringTranslation = null;
                return true;
            }
            stringKey = stringTranslation = null;
            return false;
        },
        lwReInitPlugins: function ($responseTemplate) {
            var $reInit = $responseTemplate.find('[data-lw-plugin]');
            if ($reInit.length) {
                $.each($reInit, function (index, element) {
                    var $element = $(element);
                    window.lwPluginFuncs[$element.data('lw-plugin')]('[data-lw-plugin=' + $element.data('lw-plugin') + ']');
                });
            }
        },
        /**
         * Get localized number
         *
         * @param  number numberValue
         * @since 04 MAY 2022
         * @updated 19 DEC 2022
         * @return number
         *-------------------------------------------------------- */
        formatAsLocaleNumber: function (numberValue) {
            if (localeNumberFormat && _.isNumber(numberValue)) {
                return localeNumberFormat.format(numberValue);
            } else {
                return numberValue;
            }
        },
    };

    __globals['clog'] = __Utils.clog;

    window.__DataRequest = {
        __formValidateInit: function ($thisForm) {
            $thisForm.find('label.lw-validation-error').hide();
            $thisForm.validate({
                errorClass: "lw-validation-error",
                errorPlacement: function (error, element) {
                    var $element = $(element);
                    if ($('.lw-error-container-' + $element.prop('name')).length) {
                        $('.lw-error-container-' + $element.prop('name')).addClass('lw-validation-error').html(error).show();
                    } else if ($element.siblings('.input-group-prepend').length || $element.siblings('.input-group-append').length) {
                        error.insertAfter($element.parents('.input-group')).show();
                    } else if ($(element).parent().hasClass('selectize-input')) {
                        error.insertAfter($element.parents('.selectize-input')).show();
                    } else {
                        error.insertAfter(element).show();
                    }
                    $element = error = null;
                }

            });
            return $thisForm;
        },
        __processSubmitForm: function ($this, $thisForm) {
            $thisForm = __DataRequest.__formValidateInit($thisForm);
            if ($thisForm.valid()) {
                if ($thisForm.data('show-processing')) {
                    $thisForm.addClass('lw-form-in-process').prepend('<div class="lw-spinner-box"><div class="text-center align-middle"><div class="lds-ring"><div></div><div></div><div></div><div></div></div><div></div>');
                }

                if ($this.data('action')) {
                    $thisForm.attr('action', $this.data('action'));
                }

                __DataRequest.process($thisForm).then(function (responseData) {
                    if ($thisForm.data('show-processing')) {
                        var responseJSONData = responseData.responseJSON;
                        // don't remove the disabled attribute and form in process class if response is for redirect
                        if ((responseJSONData.reaction == 21) || (responseJSONData.response_action && (responseJSONData.response_action.type == 'redirect'))) {
                        } else {
                            $thisForm.removeClass('lw-form-has-errors').removeClass('lw-form-in-process').removeClass($thisForm.data('error-class')).find('.lw-spinner-box').remove();
                        }
                    }
                });
            } else {
                $thisForm.addClass('lw-form-has-errors').addClass($thisForm.data('error-class'));
                return false;
            }
        },
        process: function ($this) {
            var isFormRequest = $this.is('form'),
                requestMethod = $this.data('method') ? $this.data('method') : ($this.attr('method') ? $this.attr('method') :  ((isFormRequest === true) ? 'post' : 'get')),
                unsecuredFields = $this.data('unsecured-fields'),
                isSecuredForm = $this.data('secured'),
                requestURL = isFormRequest ? $this.attr('action') : ($this.data('action') ? $this.data('action') : $this.attr('href')),
                processFormFieldsOptions = {};
            if (unsecuredFields) {
                processFormFieldsOptions.unsecuredFields = unsecuredFields.split(',');
            }
            if (isSecuredForm == true) {
                var inputData = __InputSecurity.processFormFields(__Utils.queryConvertToObject(
                    (isFormRequest === true) ? $this.serialize() : $this.data('post-data')
                ), processFormFieldsOptions);
            } else {
                var inputData = (isFormRequest === true) ? $this.serialize() : $this.data('post-data');
            }

            var responseCallback = _.get(window, $this.data('callback')),
                optionsForRequest = {
                    thisScope: $this,
                };

            if (_.isUndefined(responseCallback)) {
                responseCallback = null;
            }
            // callback before form submit
            // it returns values are assigned to form inputs
            if ($this.data('pre-callback')) {
                optionsForRequest['preCallback'] = _.get(window, $this.data('pre-callback'))
            }
            // callback parameters
            optionsForRequest['callbackParams'] = null;
            // check it present
            if ($this.data('callback-params')) {
                optionsForRequest['callbackParams'] = $this.data('callbackParams');
            }

            if ($this.data('showMessage')) {
                optionsForRequest['showMessage'] = $this.data('showMessage')
            }
            optionsForRequest['responseTemplate'] = null;
            if ($this.data('responseTemplate')) {
                optionsForRequest['responseTemplate'] = $this.data('responseTemplate')
            }
            var returnRequest = __DataRequest.__protectedAjaxProcess(requestURL, inputData, responseCallback, requestMethod, optionsForRequest);
            isFormRequest = requestMethod = unsecuredFields = isSecuredForm = requestURL = processFormFieldsOptions = processFormFieldsOptions = inputData = responseCallback = optionsForRequest = null;
            return returnRequest;
        },

        /**
         * Post request
         */
        post: function (requestURL, inputData, responseCallback, options) {
            inputData = inputData ? inputData : {};
            responseCallback = responseCallback ? responseCallback : null;
            if (!options) {
                var options = {};
            }
            var unsecuredFields = options['unsecuredFields'] ? options['unsecuredFields'] : [],
                isSecuredRequest = options['secured'] ? options['secured'] : false,
                processFormFieldsOptions = {};
            if (unsecuredFields) {
                processFormFieldsOptions.unsecuredFields = unsecuredFields;
            }
            if (isSecuredRequest == true) {
                inputData = __InputSecurity.processFormFields(inputData, processFormFieldsOptions);
            }
            var returnRequest = __DataRequest.__protectedAjaxProcess(requestURL, inputData, responseCallback, 'post', options);
            requestURL = inputData = responseCallback = options = null;
            return returnRequest;
        },

        get: function (requestURL, inputData, responseCallback, options) {
            inputData = inputData ? inputData : {};
            responseCallback = responseCallback ? responseCallback : null;
            var returnRequest = __DataRequest.__protectedAjaxProcess(requestURL, inputData, responseCallback, 'get', options);
            requestURL = inputData = responseCallback = options = null;
            return returnRequest;
        },

        __protectedAjaxProcess: function (requestURL, inputData, responseCallback, requestMethod, options) {
            var _thisDeferred = $.Deferred();

            if (!options) {
                options = {};
            }

            inputData = __Utils.queryConvertToObject(inputData);

            options = _.assign({
                csrf: true,
                thisScope: $(this),
                preCallback: null,
                showMessage: false,
                /*
                useful for the nested key values serverside validations
                */
                'trimKeyName': null
            }, options);

            var headers = {};
            if (options.thisScope instanceof jQuery) {
                var $thisScope = options.thisScope;
            } else {
                var $thisScope = $(options.thisScope);
            }

            $thisScope.data('is-request-processing', true).addClass('lw-form-processing');;

            if (options.csrf === true) {
                headers['X-CSRF-TOKEN'] = appConfig.csrf_token;
            }

            if (options.preCallback && _.isFunction(options.preCallback)) {
                inputData = options.preCallback(inputData, $thisScope);
            }

            $.ajax({
                type: requestMethod ? requestMethod : 'get',
                // context: this,
                url: requestURL,
                dataType: "JSON",
                data: inputData ? inputData : {},
                headers: headers,
                error: function (errorResponse) {
                    $thisScope.prop('disabled', false).removeClass('disabled');
                    $thisScope.data('is-request-processing', false).removeClass('lw-form-processing');
                    _thisDeferred.resolve(errorResponse);
                    showErrorMessage(errorResponse.responseJSON && errorResponse.responseJSON.message ? errorResponse.responseJSON.message : (errorResponse.responseJSON && errorResponse.responseJSON.data && errorResponse.responseJSON.data.message ? errorResponse.responseJSON.data.message : 'Message not available'));
                    if (errorResponse.status === 422) {
                        $.each(errorResponse.responseJSON.errors, function (key, value) {
                            // Convert dots(.) to square brackets
                            var underscoreKey = key;
                            if (_.includes(key, '.')) {
                                underscoreKey = key.replace(/\.(.+?)(?=\.|$)/g, function (m, s) { return ("_" + s); });
                                key = key.replace(/\.(.+?)(?=\.|$)/g, function (m, s) { return ("[" + s + "]"); });
                                if (options.trimKeyName) {
                                    underscoreKey = underscoreKey.replace(options.trimKeyName, '');
                                }
                            }
                            if ($thisScope.find('#' + underscoreKey + '-error').length) {
                                $thisScope.find('#' + underscoreKey + '-error').text(value).show();
                            } else {
                                if ($('.lw-error-container-' + $thisScope.find('[name="' + key + '"]').prop('name')).length) {
                                    $('.lw-error-container-' + $thisScope.find('[name="' + key + '"]').prop('name')).addClass('lw-validation-error').html(value).show();
                                } else if ($thisScope.find('.input-group-prepend ~ [name="' + key + '"]').length || $thisScope.find('[name="' + key + '"] ~ .input-group-append').length) {
                                    $('<div id="' + underscoreKey + '-error" class="lw-validation-error">' + value + '</div>').insertAfter($thisScope.find('[name="' + key + '"]').parents('.input-group')).show();
                                } else if ($thisScope.find('div > [name="' + key + '"]').length) {
                                    $('<div id="' + underscoreKey + '-error" class="lw-validation-error">' + value + '</div>').insertAfter($($thisScope.find('[name="' + key + '"]')[0]).parent()).show();
                                } else if ($thisScope.find('[name="' + key + '"] ~ .selectize-control').length) {
                                    $('<div id="' + underscoreKey + '-error" class="lw-validation-error">' + value + '</div>').insertAfter($thisScope.find('[name="' + key + '"] ~ .selectize-control')).show();
                                } else {
                                    $('<div id="' + underscoreKey + '-error" class="lw-validation-error">' + value + '</div>').insertAfter($thisScope.find('[name="' + key + '"]')[0]).show();
                                }
                            }
                        });
                    } else {
                        if (errorResponse.responseJSON && !errorResponse.responseJSON.reaction) {
                            __Utils.displayInTabWindow(errorResponse.responseJSON);
                        }
                    }
                },
                beforeSend: function () {
                    if (options.responseTemplate) {
                        var $responseTemplate = $(options.responseTemplate),
                            $parentForm = $responseTemplate.parents('form.lw-form');
                        if ($parentForm.length) {
                            $parentForm.addClass('lw-form-processing');
                        }
                    }

                    $thisScope.prop('disabled', 'disabled').addClass('disabled');
                    // Handle the beforeSend event
                    __Utils.time("DataRequest." + requestMethod + ' ' + requestURL + ' ');
                },
                success: function (successResponse) {
                    successResponse = __InputSecurity.processResponseData(successResponse);
                    // check if the response template is present and reaction is 1
                    // if so forward data accordingly
                    if (options.responseTemplate && (successResponse.reaction == 1)) {
                        var $templateStructure = $('script' + options.responseTemplate + '-template'),
                            $responseTemplate = $(options.responseTemplate),
                            compiledTemplate = _.template($templateStructure.html());
                        $responseTemplate.html(compiledTemplate(successResponse.data));

                        if ($responseTemplate.find('.lw-file-uploader').length) {
                            window.initUploader();
                        }
                        // check if any function needs to call to initialize
                        __Utils.lwReInitPlugins($responseTemplate);
                        var $parentForm = $responseTemplate.parents('form.lw-form');
                        if ($parentForm.length) {
                            $parentForm.removeClass('lw-form-processing');
                        }
                    }
                    var showMessage = false;
                    if (successResponse.show_message || options.showMessage || (successResponse.data && successResponse.data.show_message)) {
                        showMessage = true;
                    }
                    if (window.__globals.default_show_message === true) {
                        if (successResponse.hide_message || options.hideMessage) {
                            showMessage = false;
                        } else {
                            showMessage = true;
                        }
                    }

                    if (successResponse.message && showMessage) {
                        if ((successResponse.reaction_code == 1) || (_.get(successResponse, 'messageType') === 'success')) {
                            showSuccessMessage(successResponse.message);
                        } else if ((successResponse.reaction_code == 14) || (_.get(successResponse, 'messageType') === 'warning')) {
                            showWarnMessage(successResponse.message);
                        } else {
                            showErrorMessage(successResponse.message);
                        }
                    } else {
                        if (successResponse.data && showMessage) {
                            var messageItem = successResponse.data.message ? successResponse.data.message : null;
                            if (messageItem) {
                                if ((successResponse.reaction == 1) || (_.get(successResponse, 'data.messageType') === 'success')) {
                                    showSuccessMessage(messageItem);
                                } else if ((successResponse.reaction == 14) || (_.get(successResponse, 'data.messageType') === 'warning')) {
                                    showWarnMessage(messageItem);
                                } else {
                                    showErrorMessage(messageItem);
                                }
                            }

                        }
                    }
                    if (successResponse.data && successResponse['client_models'] && !_.isEmpty(successResponse['client_models'])) {
                        __DataRequest.updateModels(successResponse['client_models']);
                    }
                    if (successResponse.data && successResponse.data.errors) {
                        $.each(successResponse.data.errors, function (key, value) {

                            // Convert dots(.) to square brackets
                            var underscoreKey = key;
                            if (_.includes(key, '.')) {
                                underscoreKey = key.replace(/\.(.+?)(?=\.|$)/g, function (m, s) { return ("_" + s); });
                                key = key.replace(/\.(.+?)(?=\.|$)/g, function (m, s) { return ("[" + s + "]"); });
                                if (options.trimKeyName) {
                                    underscoreKey = underscoreKey.replace(options.trimKeyName, '');
                                }
                            }

                            if ($thisScope.find('#' + underscoreKey + '-error').length) {
                                $thisScope.find('#' + underscoreKey + '-error').text(value).show();
                            } else {
                                if ($('.lw-error-container-' + $thisScope.find('[name="' + key + '"]').prop('name')).length) {
                                    $('.lw-error-container-' + $thisScope.find('[name="' + key + '"]').prop('name')).addClass('lw-validation-error').html(value).show();
                                } else if ($thisScope.find('.input-group-prepend ~ [name="' + key + '"]').length || $thisScope.find('[name="' + key + '"] ~ .input-group-append').length) {
                                    $('<div id="' + underscoreKey + '-error" class="lw-validation-error">' + value + '</div>').insertAfter($thisScope.find('[name="' + key + '"]').parents('.input-group')).show();
                                } else if ($thisScope.find('div > [name="' + key + '"]').length) {
                                    $('<div id="' + underscoreKey + '-error" class="lw-validation-error">' + value + '</div>').insertAfter($($thisScope.find('[name="' + key + '"]')[0]).parent()).show();
                                } else if ($thisScope.find('[name="' + key + '"] ~ .selectize-control').length) {
                                    $('<div id="' + underscoreKey + '-error" class="lw-validation-error">' + value + '</div>').insertAfter($thisScope.find('[name="' + key + '"] ~ .selectize-control')).show();
                                } else {
                                    $('<div id="' + underscoreKey + '-error" class="lw-validation-error">' + value + '</div>').insertAfter($thisScope.find('[name="' + key + '"]')[0]).show();
                                }
                            }
                        });
                    }

                    //check if redirect reaction and redirect when url is present
                    if (successResponse.reaction == 21) {
                        if (_.has(successResponse.data, 'reloadPage') && (successResponse.data.reloadPage === true)) {
                            _.delay(function () {
                                __Utils.viewReload();
                            }, 500);
                        } else if (_.has(successResponse.data, 'redirectUrl')) {
                            window.location = successResponse.data.redirectUrl;
                        } else if (_.has(successResponse.data, 'redirect_to')) {
                            window.location = successResponse.data.redirect_to;
                        }
                    }
                    if (responseCallback && typeof responseCallback === 'function') {
                        responseCallback(successResponse, options.callbackParams, $thisScope);
                    }

                    if (successResponse.response_action) {
                        if (successResponse.response_action.type === 'redirect') {
                            if (window['CURRENT_PAGE_URL'] && successResponse.response_action.url === window['CURRENT_PAGE_URL']) {
                                window.location.reload();
                            } else {
                                window.location = successResponse.response_action.url;
                            }
                        } else if (successResponse.response_action.type === 'replace') {
                            $(successResponse.response_action.target).html(
                                successResponse.response_action.content
                            );
                            // change the browser url and window title etc.
                            if ($thisScope.hasClass('lw-action-with-url')) {
                                /*
                                if needed ad following block to your project if you want to back and forward button should work
                                // as state is set programmatically we needs to force back button work.
                                $(window).on('popstate', function(event) {
                                    location.reload();
                                });
                                */
                                // set the window title
                                document.title = $thisScope.data('title') ? $thisScope.data('title').trim() : $thisScope.text().trim();
                                // set the url
                                window.history.pushState(successResponse, "", requestURL);
                                // Trigger the event. Do not use dot in name
                                $(document).trigger('lw_events_ajax_success_replace',  {
                                    response: successResponse,
                                    scopeElement:$thisScope
                                });
                                var urlHashValue = (requestURL ? new URL(requestURL).hash : '').trim();
                                $('html, body').stop().animate({
                                    scrollTop: ($(urlHashValue ? urlHashValue : 'body').offset().top)
                                }, 500, 'easeInOutExpo');
                            }
                        }
                    }
                     // Trigger the event. Do not use dot in name
                    // if set at the link (data-event-callback) level
                    if ($thisScope.data('eventCallback')) {
                        $(document).trigger($thisScope.data('eventCallback'),  {
                            response: successResponse,
                            scopeElement:$thisScope
                        });
                    }
                    // Trigger the event. Do not use dot in name
                    $(document).trigger('lw_events_ajax_success',  {
                        response: successResponse,
                        scopeElement:$thisScope
                    });
                    successResponse = null;
                },
                complete: function (requestResponse) {
                    // note: __pr not working here use console.log
                    var responseData = __InputSecurity.processResponseData(requestResponse.responseJSON);
                    _thisDeferred.resolve(_.assign(requestResponse, {
                        responseJSON: responseData
                    }));
                    // don't remove the disabled attribute and form in process class if response is for redirect
                    if ((responseData.reaction == 21) || (responseData.response_action && (responseData.response_action.type == 'redirect'))) {
                    } else {
                        $thisScope.prop('disabled', false).removeClass('disabled');
                        $thisScope.data('is-request-processing', false).removeClass('lw-form-processing');
                    }
                    __Utils.timeEnd("DataRequest." + requestMethod + ' ' + requestURL + ' ');
                    // open email debug view if available
                    if (responseData && responseData.__emailDebugView) {
                        __Utils.openEmailDebugView(responseData.__emailDebugView);
                    }
                    // check if __dd is performed
                    if (responseData && responseData.__dd) {
                        __Utils.jsdd(responseData);
                    }
                    requestResponse = null;
                }
            });
            return _thisDeferred.promise();
        },
        updateModels: function (scopeName, dataObject) {
            if (scopeName && _.isObject(scopeName)) {
                dataObject = scopeName;
                scopeName = '';
            } else if (!scopeName || !_.isString(scopeName)) {
                scopeName = '';
            } else {
                scopeName = scopeName + '.';
            }
            if (dataObject && !_.isObject(dataObject)) {
                __Utils.error('dataObject should be present as object');
            }
            // get the alpineJS js data models
            var alpineXDataElements = document.querySelectorAll('[x-data]'),
                sizeOfDataObject = _.size(dataObject),
                countIndex = 1;
            // go through each item
            for (var key in dataObject) {
                if (dataObject && dataObject.hasOwnProperty(key)) {
                    var element = dataObject[key];
                    // alpineJS data models update
                    // it should be in object form
                    if (alpineXDataElements.length) {
                        _.each(alpineXDataElements, function (el) {
                            // AlpineJs version 3 compatible - 05 JUL 2021
                            if (el._x_dataStack && el._x_dataStack[0] && (!_.isUndefined(el._x_dataStack[0][scopeName + key]))) {
                                el._x_dataStack[0][scopeName + key] = element;
                            }
                            if (countIndex === sizeOfDataObject) {
                                // check if any function needs to call to initialize
                                _.delay(function () {
                                    var $lwPlugins = $('[data-lw-plugin-on-model-update]');
                                    if ($lwPlugins.length) {
                                        $.each($lwPlugins, function (index, element) {
                                            var $element = $(element);
                                            window.lwPluginFuncs[$element.data('lw-plugin-on-model-update')]('[data-lw-plugin-on-model-update=' + $element.data('lw-plugin-on-model-update') + ']');
                                        });
                                    }
                                }, 500);
                            }
                            countIndex++;
                        });
                    }
                    // alpineJS models update end
                    var $elements = $.find('[data-model="' + scopeName + key + '"]');
                    if ($elements.length) {
                        $.each($elements, function (index, elementItem) {
                            var $elementItem = $(elementItem);
                            if ($elementItem.is('input:radio') || $elementItem.is('input:checkbox')) {
                                if (element && ($elementItem.val() == element)) {
                                    $elementItem.prop('checked', true);
                                } else {
                                    $elementItem.prop('checked', false);
                                }
                            } else if ($elementItem.is('input') || $elementItem.is('select')) {
                                $elementItem.val(element);
                            } else {
                                $elementItem.text(element);
                            }
                            $elementItem = null;
                        });
                    }
                    var $htmlElements = $.find('[data-model-html="' + scopeName + key + '"]');
                    if ($htmlElements.length) {
                        $.each($htmlElements, function (index, elementItem) {
                            var $htmlElementItem = $(elementItem);
                            $htmlElementItem.html(element);
                            $htmlElementItem = null;
                        });
                    }
                    // show element if
                    var $ifShowHtmlElements = $.find('[data-show-if="' + scopeName + key + '"]');
                    if ($ifShowHtmlElements.length) {
                        $.each($ifShowHtmlElements, function (index, elementItem) {
                            var $ifShowHtmlElementItem = $(elementItem),
                                evalElement = _.get(window, element);
                            if (!evalElement) {
                                evalElement = element;
                            }
                            if (evalElement) {
                                $ifShowHtmlElementItem.show();
                            } else {
                                $ifShowHtmlElementItem.hide();
                            }
                            $ifShowHtmlElementItem = null;
                        });
                    }
                    $elements = $htmlElements = $ifShowHtmlElements = element = null;
                }
            }
            scopeName = dataObject = null;
        }
    };

    /*----------------------DIRECT GLOBALS ---------------------------------------------------------------------------------- */
    /**
    * Dump and die
    * @param n number of parameters
    *
    * return void
    *-------------------------------------------------------- */
    window.__dd = function (arg1, arg2) {

        if (window.appConfig && window.appConfig.debug) {

            console.error("JS __dd --------------------------------------------------");

            var args = Array.prototype.slice.call(arguments);

            for (var i = 0; i < args.length; ++i) {
                console.debug(args[i]);
            }

            throw new Error("-------------------------------------------------- JS __dd END");
        }
    }

    /**
    * Print data
    * @param n number of parameters
    *
    * return void
    *-------------------------------------------------------- */
    window.__pr = function () {

        if (window.appConfig && window.appConfig.debug) {

            console.info("JS __pr --------------------------------------------------");

            var args = Array.prototype.slice.call(arguments);

            for (var i = 0; i < args.length; ++i) {
                console.debug(args[i]);
            }

            console.groupCollapsed("-------------------------------------------------- JS __pr END");
            console.trace();
            console.groupEnd();
        }
    }

    /*
    * for handling cookies
    */
    window.__Cookie = {

        set: function (cname, cvalue, exdays) {
            var d = new Date();
            d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
            var expires = "expires=" + d.toUTCString();
            document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
        },
        get: function (cname) {
            var name = cname + "=";
            var ca = document.cookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) == ' ') {
                    c = c.substring(1);
                }
                if (c.indexOf(name) == 0) {
                    return c.substring(name.length, c.length);
                }
            }
            return "";
        }
    }

    /**
     * Create JSON string so it can be sent via data parameters
     */
    window.toJsonString = function (params) {
        return JSON.stringify(params);
    }

    /**
      * Ajax form submission based on form submit
      *
      *-------------------------------------------------------- */
    // bind submit event for the form
    $('body').on('submit', 'form.lw-ajax-form', function (e) {
        e.preventDefault();
        var $this = $(e.target),
            $thisForm = $(this),
            confirmMessage = $thisForm.data('confirm'),
            options = $thisForm.data('confirm-options'),
            confirmParams = $thisForm.data('confirm-params');
        if (confirmMessage) {
            showConfirmation(confirmMessage, function () {
                var returnRequest = __DataRequest.__processSubmitForm($this, $thisForm);
                $this = $thisForm = null;
                confirmMessage = options = confirmParams = null;
                return returnRequest;
            }, options ? options : {}, confirmParams ? confirmParams : {});
        } else {
            var returnRequest = __DataRequest.__processSubmitForm($this, $thisForm);
            $this = $thisForm = null;
            confirmMessage = options = confirmParams = null;
            return returnRequest;
        }
    });
    /**
     * Ajax form submission based on form on change
     *
     *-------------------------------------------------------- */
    $('body').on('change', 'form.lw-ajax-form[lwSubmitOnChange]', function (e) {
        e.preventDefault();
        var $this = $(e.target),
            $thisForm = $(this);
        var returnRequest = __DataRequest.__processSubmitForm($this, $thisForm);
        $this = $thisForm = null;
        return returnRequest;
    });

    // enable for pointer events
    $('body').addClass('lw-ajax-form-ready');

    /**
      * Ajax form submission based on click
      *
      *-------------------------------------------------------- */
    $('body').on('click', '.lw-ajax-form-submit-action', function (e) {
        e.preventDefault();
        var $this = $(this),
            $thisForm = $this.parents('form');
        var returnRequest = __DataRequest.__processSubmitForm($this, $thisForm);
        $this = $thisForm = null;
        return returnRequest;
    });

    /**
    * Ajax form submission based on click
    *
    *-------------------------------------------------------- */
    $('body').on('click', '.lw-ajax-link-action', function (e) {
        e.preventDefault();
        var $this = $(this),
            confirmMessage = $this.data('confirm'),
            options = $this.data('confirm-options'),
            confirmParams = $this.data('confirm-params');
        if (confirmMessage) {
            showConfirmation(confirmMessage, function () {
                confirmMessage = options = confirmParams = null;
                return __DataRequest.process($this);
            }, options ? options : {}, confirmParams ? confirmParams : {});
        } else {
            confirmMessage = options = confirmParams = null;
            return __DataRequest.process($this);
        }
    });

    /**
    * Ajax form submission based on click
    * @deprecated 22 JUN 2021 instead use .lw-ajax-link-action with data-confirm attribute
    *-------------------------------------------------------- */
    $('body').on('click', '.lw-ajax-link-action-via-confirm', function (e) {
        e.preventDefault();
        var $this = $(this),
            confirmMessage = $this.data('confirm'),
            options = $this.data('confirm-options'),
            confirmParams = $this.data('confirm-params');
        if (confirmMessage) {
            showConfirmation(confirmMessage, function () {
                return __DataRequest.process($this);
            }, options ? options : {}, confirmParams ? confirmParams : {});
        }
        confirmMessage = options = confirmParams = null;
    });
    /*
    Init clint side form validations
    */
    $('form.lw-ajax-form').each(function () {
        __DataRequest.__formValidateInit($(this));
    });
})(window);
(function (window, undefined) {
    'use strict';
    if ($.fn.dataTable) {
        $.extend($.fn.dataTable.defaults, {
            serverSide: true,
            iCookieDuration: 60,
            paging: true,
            processing: true,
            responsive: true,
            destroy: true,
            retrieve: true,
            lengthChange: true,
            language: {
                emptyTable: "There are no records to display."
            },
            searching: false,
            ajax: {
                // any additional data to send
                data: function (additionalData) {
                    additionalData.page =
                        additionalData.start / additionalData.length + 1;
                }
            }
        });
    }

    /**
     * Initialize DataTable
     *
     * @param tableID {string} - table id
     * @param dtOptions {object} - datatable options
     * @param dsOptions {object} - datastore options
     *
     * @return datatable instance
     *-------------------------------------------------------- */

    window.dataTable = function (tableID, dtOptions, dsOptions, callbackFunction) {
        if (callbackFunction) {
            dtOptions.callbackFunction = callbackFunction;
        }

        return $(tableID).DataTable(dtConfig(dtOptions, dsOptions));
    }

    /**
     * DataTable Custom Configuration generation based on provided data
     *
     * @param object {options} - Object
     *                         url          (required)
     *                         scope        (required)
     *                         columnsData  (required)
     *                         dtOptions    (optional)
     *
     * @return array
     *-------------------------------------------------------- */

    function dtConfig(options, dsOptions) {
        var dataStoreInstance = this;

        if (!dsOptions || !_.isObject(dsOptions)) {
            dsOptions = {};
        }
        var dtOptionsCollection = {
            ajax: function (data, callback, settings) {
                // for laravel 5 paginate
                data.page = data.start / data.length + 1;

                var drawID = data.draw ? data.draw : false,
                    optionsSendToFetch = {
                        params: data,
                        fresh: dsOptions.fresh ? dsOptions.fresh : false
                    };

                if (_.has(dsOptions, "persist")) {
                    optionsSendToFetch["persist"] = dsOptions.persist;
                }

                var urlID = options.url,
                    requestURL = "";
                if (optionsSendToFetch.params) {
                    requestURL = urlID + "?" + $.param(optionsSendToFetch.params);

                    if (_.has(optionsSendToFetch.params, "draw")) {
                        delete optionsSendToFetch.params["draw"];
                    }

                    urlID = urlID + "?" + $.param(optionsSendToFetch.params);
                }
                // Send Ajax request for get datatable data
                __DataRequest.get(requestURL, {}, function (response) {
                    response.draw = drawID;

                    // callback for datatable after data fetched
                    if (
                        options.callbackFunction &&
                        _.isFunction(options.callbackFunction)
                    ) {
                        options.callbackFunction.call(this, response);
                    }

                    callback(response);
                }, {
                    thisScope: options.scope
                });
            },
            drawCallback: function (settings) {
                var api = this.api(),
                    $thisDataTable = api.table();
                if (
                    _.has($thisDataTable.columns, "adjust") &&
                    _.has($thisDataTable.responsive, "recalc")
                ) {
                    // responsive fix for datatable for cached datastore item
                    _.delay(function () {
                        $thisDataTable.columns.adjust().responsive.recalc();
                    }, 180);
                }
                // added on 27 APR 2022 and updated on 06 MAY 2022
                $('.paginate_button').not('.previous, .next').find('.page-link').each(function (i, element) {
                    var paginateNumberValue = $(element).text();
                    $(element).text(__Utils.formatAsLocaleNumber(paginateNumberValue));
                });
            },
            columns: [],
            /* "createdRow": function (nRow, data, dataIndex, cells) {
                $('td', nRow).eq(5).append('highlight');
            }, */
            // added on 27 APR 2022
            formatNumber: function (numberValue) {
                return __Utils.formatAsLocaleNumber(numberValue);
            },
            // added on 19 MAY 2016 - 0.4.0
            responsive: {
                details: {
                    renderer: function (api, rowIdx, columns) {
                        var data = _.map(columns, function (col, i) {
                            return col.hidden ?
                                '<li data-dtr-index="' +
                                col.columnIndex +
                                '" data-dt-row="' +
                                col.rowIndex +
                                '" data-dt-column="' +
                                col.columnIndex +
                                '">' +
                                '<span class="dtr-title">' +
                                col.title +
                                "</span> " +
                                '<span class="dtr-data">' +
                                col.data +
                                "</span>" +
                                "</li>" :
                                "";
                        }).join("");
                        return data;
                    }
                }
            }
        };

        if (options.dtOptions) {
            _.assign(dtOptionsCollection, options.dtOptions);
        }

        dtOptionsCollection.columns = _.map(options.columnsData, function (
            dtColumnData
        ) {
            return {
                defaultContent: "",
                data: dtColumnData.name ? dtColumnData.name : null,
                orderable: dtColumnData.orderable ? true : false,
                className : dtColumnData.className ? dtColumnData.className : null,
                render: function (subject, data, obj, settings) {
                    if (!dtColumnData.name) {
                        obj = subject;
                    } else {
                        obj.dtSubject = dtColumnData.name;
                    }
                    // if template given
                    if (dtColumnData.template) {
                        // compile data using underscore template
                        return __Utils.template(dtColumnData.template, obj);
                    } else {
                        return obj[dtColumnData.name];
                    }
                }
            };
        });

        return dtOptionsCollection;
    }

    /**
     * Ajax Reload DataTable
     *
     * @param dataTableInstance {object} - datatable instance
     *
     * @return datatable instance
     *-------------------------------------------------------- */

    window.reloadDT = function (dataTableInstance) {
        dataTableInstance.ajax.reload(null, false);

        __Utils.log("__DataStore:: DataTable reloaded");
    }

    // auto identify dataTables and initialize
    document.addEventListener('DOMContentLoaded', function () {
        // check if
        var $lwDataTable = $('[lwDataTable]');
        if ($lwDataTable.length) {
            (function initDatatable() {
                $lwDataTable.each(function (index) {
                    var $element = $(this),
                        columnsData = [],
                        elementData = ($element.data()),
                        $tableHeaders = $element.find('thead th'),
                        dtOptions = {
                            "searching": true
                        };

                    $tableHeaders.each(function (index) {
                        columnsData.push(_.assign({
                            name: _.snakeCase($(this).text())
                        }, $(this).data()));

                    });
                    // to avoid following error
                    // Uncaught TypeError: Cannot use 'in' operator to search for 'length' in g
                    $element.removeData();
                    if (elementData['pageLength'] && _.isInteger(elementData['pageLength'])) {
                        dtOptions['pageLength'] = elementData['pageLength'];
                        $.extend($.fn.dataTable.defaults, {
                            lengthMenu: _.assign($.fn.dataTable.defaults.aLengthMenu, [dtOptions['pageLength']])
                        });
                    }

                    // set the item on datatable element
                    if (elementData['order']) {
                        dtOptions['order'] = elementData['order'];
                    }

                    if (elementData['orderBy']) {
                        dtOptions['order'] = [
                            elementData['orderBy'],
                            (!_.isUndefined(elementData, 'orderType') && elementData['orderType']) ? elementData['orderType'] : 'asc'
                        ];
                    }

                    if (elementData['rowcallback']) {
                        /* instead using eval we have used this */
                        var fnToCall = window[elementData['rowcallback']];
                        dtOptions['rowCallback'] = window[elementData['rowcallback']];
                    }

                    if (_.isEmpty(columnsData)) {
                        throw Error('Columns not defined');
                    }

                    if (!elementData['url']) {
                        throw Error('DataTable url is missing, it should be on table as data-url');
                    }

                    dataTable($element, {
                        url: elementData['url'],
                        dtOptions: dtOptions,
                        columnsData: columnsData,
                        scope: $element
                    });
                });
            })();
        }
    });

})(window);
(function (window, undefined) {

    'use strict';
    /**
     * Notification Functions : 11 JAN 2020
     * LivelyWorks
     *
     *-------------------------------------------------------- */
    var notyDefaultOptions = {
        layout: 'topRight',
        theme: 'bootstrap-v4',
        progressBar: true,
        timeout: 3000
       /*  //  closeWith: ['click'],
        animation: {
            open: 'animated bounceInRight', // Animate.css class names
            close: 'animated bounceOutRight'
        }  */
    };


    /**
    * Show Success Message
    *************************************************/
    window.showSuccessMessage = function (message) {
        new Noty($.extend({}, notyDefaultOptions, {
            type: 'success',
            text: message
        })).show();
    }

    /**
    * Show Error Message
    *************************************************/
    window.showErrorMessage = function (message) {
        new Noty($.extend({}, notyDefaultOptions, {
            type: 'error',
            text: message
        })).show();
    };

    /**
    * Show Info Message
    *************************************************/
    window.showInfoMessage = function (message) {
        new Noty($.extend({}, notyDefaultOptions, {
            type: 'info',
            text: message
        })).show();
    };

    /**
    * Show Warning Message
    *************************************************/
    window.showWarnMessage = function (message) {
        new Noty($.extend({}, notyDefaultOptions, {
            type: 'warning',
            text: message
        })).show();
    };
    /**
    * Show confirmation dialog
    *************************************************/
    window.showConfirmation = function (containerId, yesCallback, options, confirmParams) {

        var $messageItem = (!_.includes(containerId, ' ')) ? $(containerId) : false,
            confirmationContainer = '';

        if ($messageItem && $messageItem.length) {
            confirmationContainer = _.template($messageItem.html());
        } else {
            confirmationContainer = containerId;
        }
        if (!options) {
            options = {};
        }

        options = _.assign({
            showCancelBtn: true,
            type: 'warning',
            confirmBtnColor: '#d33d33',
            cancelButtonText: 'Cancel',
            confirmButtonText: 'Yes'

        }, options);

        if (!confirmParams) {
            confirmParams = {};
        }

        Swal.fire({
            // title: 'Are you sure?',
            html: _.isString(confirmationContainer) ? confirmationContainer : confirmationContainer(confirmParams),
            icon: options['type'],
            showCancelButton: options['showCancelBtn'],
            confirmButtonColor: options['confirmBtnColor'], // 3085d6
            // cancelButtonColor: '#d33',
            cancelButtonText: options['cancelButtonText'],
            confirmButtonText: options['confirmButtonText']
        }).then(function (result) {
            if (result.isConfirmed) {
                yesCallback();
            }
        });
    };

    window.showAlert = function (message, type) {
        Swal.fire({
            icon: type ? type : 'info',
            text: message
        })
    };

})(window);
'use strict';
//defined pusher global variable
var pusher;

/**
 * Pusher Notify
 *
 * @param string msg
 * @param object options
 *
 * @return void
 *---------------------------------------------------------------- */
function configure(pusherAppKey, __pusherAppOptions) {
	if (!__pusherAppOptions || _.isUndefined(__pusherAppOptions)) {
		__pusherAppOptions = window.__pusherAppOptions;
	}
	//Pusher App options set location footer.blade.php and audio-video.blade.php file
	pusher = new Pusher(pusherAppKey, __pusherAppOptions);
};

/**
 * Pusher Subscribe
 *
 * @param string msg
 * @param object options
 *
 * @return void
 *---------------------------------------------------------------- */
function subscribe(channelId, channelId2, eventId, pusherAppKey, pseudoCallback, isFresh) {

	if (_.isUndefined(isFresh)) {
		isFresh = false;
	}

	//load pusher instance
	configure(pusherAppKey);

	//check push instance available or not
	if (!pusher) {
		//load pusher instance
		configure(pusherAppKey);
	}

	//subscribe pusher channel id
	var channel = pusher.subscribe(channelId);

	//check is fresh record
	if (isFresh) {
		pusher.disconnect();
		channel.unbind(eventId);
		pusher.connect();
	}

	//bind subscribe callback
	channel.bind(eventId, function (data) {
		pseudoCallback(data);
	});

	//subscribe pusher channel id
	var channel2 = pusher.subscribe(channelId2);

	//check is fresh record
	if (isFresh) {
		pusher.disconnect();
		channel2.unbind(eventId);
		pusher.connect();
	}

	//bind subscribe callback
	channel2.bind(eventId, function (data) {
		pseudoCallback(data);
	});
};

/**
 * Disconnect all
 * @return void
 *---------------------------------------------------------------- */
function disconnect() {
	//check pusher instance exist then disconnect
	if (pusher) {
		pusher.disconnect();
	}
}

/**
* Pusher Subscribe
*
* @param string msg
* @param object options
*
* @return void
*---------------------------------------------------------------- */
function accountSubscribe(eventId, pusherAppKey, userUID, optionalUId, notifyCallback, isFresh) {
	if (_.isUndefined(isFresh)) {
		isFresh = false;
	}
	subscribe('channel-' + userUID, 'channel-' + optionalUId, eventId, pusherAppKey, notifyCallback, isFresh);
}

/**
 * Pusher Subscribe
 *
 * @param string msg
 * @param object options
 *
 * @return void
 *---------------------------------------------------------------- */
function subscribeNotification(eventId, pusherAppKey, userUID, optionalUId, notifyCallback, isFresh) {

	if (_.isUndefined(isFresh)) {
		isFresh = false;
	}

	accountSubscribe(eventId, pusherAppKey, userUID, optionalUId, notifyCallback, isFresh);
}

/**
* Pusher Notification Instance
* LivelyWorks
*
*-------------------------------------------------------- */
configure('');
'use strict';
// rtc object
var rtc = {
	client: null,
	joined: false,
	published: false,
	localStream: null,
	remoteStreams: [],
	params: {}
},

//defined pusher global varibale
userType = null;
//add calling view
function addView(id, show) {
	if (!$("#remoteContainer").length) {
		$("<div/>", {
			id: "remote_video_panel",
			class: "video-view"
		}).appendTo("#video");

		$("<div/>", {
			id: "remote_video",
			class: "video-placeholder remote-video"
		}).appendTo("#remote_video_panel");

		$("<div/>", {
			id: "remote_video_info",
			class: "video-profile " + (show ? "" : "hide")
		}).appendTo("#remote_video_panel");

		$("<div/>", {
			id: "video_autoplay",
			class: "autoplay-fallback hide",
		}).appendTo("#remote_video_panel");
	}
};

//remove calling view
function removeView(id) {
    $('.remote-video').remove();
};

var __AudioVisualRequest = {
	handleCallEvents: function (rtc, userType, errorCallback, successCallBack) {
        rtc.client.on("user-published", async (remoteUser, mediaType) => {
            await rtc.client.subscribe(remoteUser, mediaType).then((tmpData) => {
                console.log(tmpData);
            });
            if (mediaType == "video") {
                console.log("subscribe video success");
                var id = remoteUser.uid;
                addView();
              remoteUser.videoTrack.play("remote_video");
            }
            if (mediaType == "audio") {
              console.log("subscribe audio success");
              remoteUser.audioTrack.play();
            }
            successCallBack({
                'stream_subscribe' : true
			});
            rtc.client.on("user-unpublished", async (remoteUser, mediaType) => {
                var id = remoteUser.uid;
                if (id != rtc.params.uid) {
                    //remove audio/video calling view
                    removeView();
                    successCallBack({
                        'peer_leave': true
                    });
                }
            });
        });
	},

	joinCall: async function (requestData, userUid, userType, errorCallback, successCallBack) {
		// Options for joining a channel
		var option = {
			appID: requestData.agoraAppID,
			channel: requestData.channel,
			uid: userUid,
			token: requestData.token
		};

        rtc.client = AgoraRTC.createClient({ mode: "live", codec: "vp8" });
        try {
            const tempUid = await rtc.client.join(option.appID,  option.channel, option.token, option.uid);
            console.log("join success -" + tempUid);
            rtc.params.uid = tempUid;

		// handle AgoraRTC client event
        __AudioVisualRequest.handleCallEvents(rtc, userType, errorCallback, successCallBack);

				//enable video by default false
				var enableVideo = false;
				//check call type is 2 (Video) then enable video
				if (requestData.callType == 2) {
					enableVideo = true;
                }
                // console.log("create local audio/video track success");
            try {
                if (enableVideo) {
                    const localVideo = await AgoraRTC.createCameraVideoTrack();
                    const localAudio = await AgoraRTC.createMicrophoneAudioTrack();
                    localVideo.play("local_stream");
                     // Remove this line if the channel profile is not live broadcast.
                    await rtc.client.setClientRole("host");
                    await rtc.client.publish([localAudio, localVideo]);
                } else {
                    const localAudio = await AgoraRTC.createMicrophoneAudioTrack();
                     // Remove this line if the channel profile is not live broadcast.
                    await rtc.client.setClientRole("host");
                    await rtc.client.publish([localAudio]);
                }
                console.log("publish success");
                successCallBack({
                    'stream_publish' : true
                });
              } catch (e) {
                __Utils.error("publish failed", e);
              }

          } catch (e) {
            __Utils.error("join failed", e);
          }

          navigator.mediaDevices.getUserMedia({
            audio: true,
            video: enableVideo
        }).then(function (stream) {}).catch(function (err) {
            // handle the error
            errorCallback({
                error : true,
                result: err
            });
        });
	},
	
	disconnectCall: async function (pseudoCallback)  {
		//check remote stream exists or not
		if (!rtc.client) {
			//success callback
			pseudoCallback({
				'peer_leave_failed' : true
			});
			return;
        }
        await rtc.client.leave();
        removeView();

        pseudoCallback({
            'call_disconnect': true
        });
	}
};
// Start of use strict
"use strict";
// Create a object for messenger
var optionalUserId = optionalLoggedInUserId;
var __Messenger = {
    sendMessageUrl: null,
    // Load Uploader instance
    loadUploaderInstance: function () {
        var pond = null,
            uniqueId = Math.random().toString(36).substr(2, 9);
        if (_.isEmpty(pond)) {
            pond = FilePond.create({
                name: 'filepond',
                labelIdle: "<i class='fas fa-paperclip'></i>",
                allowDrop: false,
                allowImagePreview: false,
                allowRevert: false,
                server: {
                    process: {
                        url: __Messenger.sendMessageUrl,
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': appConfig.csrf_token
                        },
                        withCredentials: false,
                        onload: function (response) {
                            var responseData = JSON.parse(response);
                            var requestData = responseData.data;
                            var storedData = requestData.storedData;

                            if (responseData.reaction == 1) {
                                __Messenger.replaceMessage(storedData.type, storedData.message, storedData.unique_id, storedData.created_on);
                            } else {
                                __Messenger.removeMessage(storedData.type, storedData.unique_id);
                                showErrorMessage(requestData.message);
                            }
                        },
                        ondata: function (formData) {
                            formData.append('type', 2);
                            formData.append('unique_id', uniqueId);
                            formData.append('optionalLoggedInUserId', optionalUserId);
                            return formData;
                        }
                    }
                },
                onaddfilestart: function () {
                    __Messenger.appendMessage(2, '', uniqueId);
                    $('#lwMessengerFileUpload').hide();
                    $('#lwUploadingLoader').show();
                },
                onprocessfile: function (error, file) {
                    pond.removeFile(file.id);
                    $('#lwMessengerFileUpload').show();
                    $('#lwUploadingLoader').hide();
                }
            });
            pond.appendTo(document.getElementById("lwMessengerFileUpload"));
        }
    },

    $emojiElement: null,
    // Load emoji text box container
    loadEmojiContent: function () {
        __Messenger.$emojiElement = $("#lwChatMessage").emojioneArea({
            placeholder: __Utils.getTranslation('chat_placeholder', "type message..."),
            hidePickerOnBlur: true,
            events: {
                keyup: function (editor, event) {
                    if (event.which == 13) { // On Enter
                        __Messenger.sendMessage(1, {
                            message: this.getText(),
                            type: 1,
                        });

                        this.hidePicker();
                    }
                }
            }
        });
    },

    // Open sticker bottom-sheet
    openStickerBottomSheet: function () {
        $(".lw-messenger").on("click", ".lw-open-stickers-action, .lw-open .lw-overlay", function () {
            $('.lw-messenger-bottom-sheet').toggleClass("lw-open");
            __Messenger.getStickers();
        });
    },

    // Show bottom-sheet fro stickers
    getStickers: function () {
        var $lwBottomSheetHeadingContainer = $('.lw-heading'),
            $lwStickerImagesContainer = $("#lwStickerImagesContainer");

        $lwBottomSheetHeadingContainer.html("");
        $lwStickerImagesContainer.html("");
        $('#lwGifImagesContainer').html("");

        // Set Heading of bottom sheet
        $lwBottomSheetHeadingContainer.append('<h5><i class="fas fa-sticky-note text-success"></i> ' + __Utils.getTranslation('sticker_name_label', 'Stickers') + '</h5>');
    },

    // Fetch Stickers from server
    fetchStickers: function (responseData) {
        // Get sticker response data from server
        var stickers = responseData.data.stickers;
        // check if stickers exists
        if (!_.isEmpty(stickers)) {
            _.forEach(stickers, function (sticker) {
                // Create Image tag
                stickerImageTag = "<span class='lw-buy-sticker-container'><img height='100px' width='110px' src='" + sticker.image_url + "' id='" + sticker.id + "' class='lw-sticker-image' data-is-free='" + sticker.is_free + "' data-is-purchased='" + sticker.is_purchased + "'>";

                // check if sticker is free
                if (sticker.is_free) {
                    stickerImageTag += "<span class='text-center'>Free</span>";
                } else if (!sticker.is_purchased) {
                    stickerImageTag += "<div id='lwBuyNowStickerBtn-" + sticker.id + "' class='text-center'><span>" + sticker.formatted_price + "</span><br><button type='button' class='btn btn-secondary btn-sm' onclick='__Messenger.buySticker(" + sticker.id + ")'>Buy Now</button></span></div>";
                } else if (sticker.is_purchased) {
                    stickerImageTag += "<span class='text-center'>Purchased</span>";
                }

                $('#lwStickerImagesContainer').append(stickerImageTag);
            });
        } else {
            $('#lwStickerImagesContainer').append("<?= __tr('No result found.') ?>");
        }
        // Send selected sticker
        $('.lw-sticker-image').on('click', function () {
            if ($(this).data('is-free') || $(this).data('is-purchased')) {
                __Messenger.sendMessage(12, {
                    message: this.currentSrc,
                    type: 12,
                    item_id: this.id
                });
                $('.lw-messenger-bottom-sheet').toggleClass("lw-open");
            } else {
                __Messenger.buySticker(this.id);
            }
        });
    },

    // Buy sticker
    buySticker: function (stickerId) {
        showConfirmation($('#lwBuyStickerText').data('message'), function () {
            __DataRequest.post(__Messenger.buyStickerUrl, {
                sticker_id: stickerId
            }, function (responseData) {
                if (responseData.reaction == 1) {
                    $("#lwTotalCreditWalletAmt").html(responseData.data.availableCredits)
                    $('#lwBuyNowStickerBtn-' + stickerId).replaceWith("<span class='text-center'>Purchased</span>");
                }
                $("#lwStickerImagesContainer").html("");
                __Messenger.fetchStickers(responseData.data.stickers);
            });
        }, {
            id: 'lwBuyStickerAlert'
        });
    },

    // Open gif bottom-sheet
    openGifBottomSheet: function () {
        $(".lw-messenger").on("click", ".lw-open-gif-action, .lw-open .lw-overlay", function () {
            $('.lw-messenger-bottom-sheet').toggleClass("lw-open");
            __Messenger.getGifImagesContent();
        });
    },

    // Get gif images
    getGifImagesContent: function () {
        var $lwBottomSheetHeadingContainer = $('.lw-heading');
        $lwBottomSheetHeadingContainer.html("");
        // Set Heading of bottom sheet
        $lwBottomSheetHeadingContainer.append('<h5><i class="fa fa-images text-success"></i> '+ __Utils.getTranslation('send_gif', 'Send GIF') +'</h5><div class="input-group lw-gif-search-input"><input type="text" class="form-control" name="search" id="lwSearchInput" value="" placeholder="'+__Utils.getTranslation('search_gif', 'Search GIF')+'"><div class="input-group-append"><button type="button" class="btn btn-secondary" onclick="__Messenger.searchGifImages()"><i class="fas fa-search"></i></button></div></div>');
        __Messenger.fetchGifImages();
    },

    // Search for images
    searchGifImages: function () {
        var searchValue = $('#lwSearchInput').val();
        __Messenger.fetchGifImages({ searchValue: searchValue });
    },

    // Fetch Gif Images
    fetchGifImages: function (queryOptions) {
        $("#lwStickerImagesContainer").html("");
        $lwGifImagesContainer = $('#lwGifImagesContainer');
        $lwGifImagesContainer.html('<div class="lw-messenger-image-loading"></div>');
        var queryURL = '';
        params = {
            limit: 50,
            api_key: __Messenger.giphyKey,
            fmt: "json"
        };

        // check if query options exists
        if (!_.isUndefined(queryOptions)) {
            queryURL = "https://api.giphy.com/v1/gifs/search?";
            params.q = queryOptions.searchValue;
        } else {
            queryURL = "https://api.giphy.com/v1/gifs/trending?";
        }
        // Get data from gify server
        __DataRequest.get(queryURL + $.param(params), {}, function (response) {
            var gifImages = response.data;
            $lwGifImagesContainer.html('');
            if (!_.isEmpty(gifImages)) {
                _.forEach(gifImages, function (gif) {
                    var imageTag = $("<img>");
                    imageTag.attr({
                        height: "100px",
                        width: "100px",
                        src: gif.images.preview_gif.url,
                        class: 'lw-gif-image lw-lazy-img'
                    });
                    $lwGifImagesContainer.append(imageTag);
                });
            } else {
                $lwGifImagesContainer.append(__Utils.getTranslation('gif_no_result', 'Result Not Found'));
            }
            // after click on gif image perform some action
            $('.lw-gif-image').on('click', function () {
                var gifImage = $("<img>");
                gifImage.attr({
                    height: "100px",
                    width: "100px",
                    src: this.currentSrc
                });

                __Messenger.sendMessage(12, {
                    message: this.currentSrc,
                    type: 8
                });
                $('.lw-messenger-bottom-sheet').toggleClass("lw-open");
            });
        }, { csrf: false });
    },

    // Accept message request
    acceptMessageRequest: function () {
        $('#lwSendMessageForm').show();
        $('#lwAcceptChatRequestBtn').hide();
        $('#lwDeclineChatRequestBtn').hide();
        __Messenger.hideShowDropdownButtons(true);
    },

    // Decline Message Request
    declineMessageRequest: function () {
        $('#lwDeclineChatRequestBtn').hide();
        __Messenger.hideShowDropdownButtons(false);
    },

    // Prepare send button instance
    createSendButtonInstance: function () {
        $('#lwSendMessageButton').on('click', function () {
            __Messenger.sendMessageViaForm();
        });
    },

    // send message via form
    sendMessageViaForm: function () {
        var message = '',
            messageFormData = $('#lwSendMessageForm').serializeArray();
        _.forEach(messageFormData, function (item, index) {
            if (item.name == 'message') {
                message = item.value;
            }
        });
        __Messenger.sendMessage(1, {
            message: message,
            type: 1,
        });
    },

    // Send message
    sendMessage: function (type, formData) {
        var uniqueId = Math.random().toString(36).substr(2, 9),
            message = formData.message;
        if (!_.isEmpty(message)) {
            __Messenger.appendMessage(type, message, uniqueId);
        } else {
            showErrorMessage(__Utils.getTranslation('message_is_required', 'Message is required'));
            __Utils.throwError('Message is required');
        }
        formData.unique_id = uniqueId;
        formData.optionalLoggedInUserId = optionalLoggedInUserId;

        __DataRequest.post(__Messenger.sendMessageUrl, formData, function (responseData) {
            var requestData = responseData.data,
                storedData = requestData.storedData;
            if (responseData.reaction == 1) {
                __Messenger.replaceMessage(storedData.type, storedData.message, storedData.unique_id, storedData.created_on);
            } else {
                __Messenger.removeMessage(storedData.type, storedData.unique_id);
            }
        });
    },

    // Append message to message board
    appendMessage: function (type, message, uniqueId) {
        var $messengerChatWindow = $('.lw-messenger-chat-window'),
            appendText = '';

        if (type == 1) {
            appendText = '<div class="lw-messenger-chat-message lw-messenger-chat-sender row col-md-12" id="' + uniqueId + '"><p class="lw-messenger-chat-item lw-messenger-new-message">' + message + '<span class="lw-messenger-chat-meta">Now</span></p><img src="' + __Messenger.loggedInUserProfilePicture + '" class="lw-profile-picture lw-online" alt=""></div>';
        } else {
            appendText = '<div class="lw-messenger-chat-message lw-messenger-chat-sender row col-md-12" id="' + uniqueId + '"><p class="lw-messenger-chat-item lw-messenger-new-message"><p class="lw-messenger-chat-item lw-messenger-new-message col-md-8"><span class="lw-messenger-image-loading"> loading ...please wait</span><span class="lw-messenger-chat-meta">10 February 2020 at 4:00 pm</span></p><span class="lw-messenger-chat-meta">Now</span></p><img src="' + __Messenger.loggedInUserProfilePicture + '" class="lw-profile-picture lw-online" alt=""></div>';
        }

        $messengerChatWindow.append(appendText);
        __Messenger.$emojiElement[0].emojioneArea.setText('');

        $(".lw-messenger-chat-window").scrollTop(1000000);
    },

    // replace message with existing message
    replaceMessage: function (type, message, uniqueId, createdOn) {
        if (type != 1) {
            var replaceContainer = '<div class="lw-messenger-chat-message lw-messenger-chat-sender row col-md-12"><p class="lw-messenger-chat-item lw-messenger-new-message"><img src="' + message + '" alt=""><span class="lw-messenger-chat-meta">' + createdOn + '</span></p><img src="' + __Messenger.loggedInUserProfilePicture + '" class="lw-profile-picture lw-online" alt=""></div>';

            $('#' + uniqueId).replaceWith(replaceContainer);
        }
    },

    // Remove message from message board
    removeMessage: function (type, uniqueId) {
        if (type != 1) {
            $('#' + uniqueId).remove();
        }
    },

    // Append received message
    appendReceivedMessage: function (type, message, createdOn) {
        var $messengerChatWindow = $('.lw-messenger-chat-window'),
            appendText = '';
        if (type == 1) {
            appendText = '<div class="lw-messenger-chat-message align-self-center row col-md-12 lw-messenger-chat-recipient"><img src="' + __Messenger.recipientUserProfilePicture + '" class="lw-profile-picture lw-online" alt=""><p class="lw-messenger-chat-item lw-messenger-new-message col-md-8">' + message + '<span class="lw-messenger-chat-meta">' + createdOn + '</span></p></div>';
        } else {
            appendText = '<div class="lw-messenger-chat-message align-self-center row col-md-12 lw-messenger-chat-recipient"><img src="' + __Messenger.recipientUserProfilePicture + '" class= "lw-profile-picture lw-online" alt=""><p class="lw-messenger-chat-item lw-messenger-new-message col-md-8"><img src="' + message + '" alt=""><span class="lw-messenger-chat-meta">' + createdOn + '</span></p></div>';
        }
        $messengerChatWindow.append(appendText);
        $(".lw-messenger-chat-window").scrollTop(1000000);
    },

    // Hide / Show sidebar on mobile view
    toggleSidebarOnMobileView: function () {
        if ($('.lw-messenger').hasClass('lw-messenger-sidebar-opened')) {
            $('.lw-messenger').removeClass('lw-messenger-sidebar-opened');
        } else {
            $('.lw-messenger').addClass('lw-messenger-sidebar-opened');
        }
    },

    // Click on toggle button
    hideShowChatSidebar: function () {
        $('#lwChatSidebarToggle').on('click', function () {
            __Messenger.toggleSidebarOnMobileView();
        });
    },

    // Show hide disable enable buttons
    hideShowDropdownButtons: function (showButtons) {
        if (showButtons) {
            // For delete all chat button
            $('#lwDeleteAllChatActiveButton').show();
            $('#lwDeleteAllChatDisableButton').hide();

            // Audio call button 
            $('#lwAudioCallBtn').show();
            $('#lwAudioCallDisableBtn').hide();

            // video Call button
            $('#lwVideoCallBtn').show();
            $('#lwVideoCallDisableBtn').hide();
        } else {
            // For delete all chat button
            $('#lwDeleteAllChatActiveButton').hide();
            $('#lwDeleteAllChatDisableButton').show();

            // Audio call button 
            $('#lwAudioCallBtn').hide();
            $('#lwAudioCallDisableBtn').show();

            // video Call button
            $('#lwVideoCallBtn').hide();
            $('#lwVideoCallDisableBtn').show();
        }
    },
    showMessageRequestNotification: function () {
        //show dialog
        $(".lw-messenger #lwAudioCallDisableBtn, .lw-messenger #lwVideoCallDisableBtn").on("click", function () {
            $('.lw-messenger #lwUserNotAcceptedMsgRequest').modal({
                keyboard: false
            });
        });

        //hide dialog
        $(".lw-messenger .lw-not-accepted-dialog-close-btn, .lw-messenger .lw-not-accepted-dialog-close-btn").on("click", function () {
            $('.lw-messenger #lwUserNotAcceptedMsgRequest').modal('hide');
        });
    }
};

function handleMessageActionContainer(messageRequestStatus, loadUploaderInstance) {
    if (messageRequestStatus == 'MESSAGE_REQUEST_ACCEPTED'
        || messageRequestStatus == 'SEND_NEW_MESSAGE') {
        if (loadUploaderInstance) {
            __Messenger.loadUploaderInstance();
        }
        __Messenger.hideShowDropdownButtons(true);
        $('#lwSendMessageForm').show();
        $('#lwDeclineMessage').hide();
    } else if (messageRequestStatus == 'MESSAGE_REQUEST_RECEIVED') {
        $('#lwSendMessageForm').hide();
        $('#lwAcceptChatRequestBtn').show();
        $('#lwDeclineChatRequestBtn').show();
        __Messenger.hideShowDropdownButtons(false);
        if (loadUploaderInstance) {
            __Messenger.loadUploaderInstance();
        }
    } else if (messageRequestStatus == 'MESSAGE_REQUEST_DECLINE') {
        __Messenger.hideShowDropdownButtons(false);
        $('#lwAcceptChatRequestBtn').show();
    } else if (messageRequestStatus == 'MESSAGE_REQUEST_DECLINE_BY_USER') {
        $('#lwSendMessageForm').hide();
        $('#lwDeclineMessage').show();
    } else if (messageRequestStatus == 'MESSAGE_REQUEST_SENT') {
        if (loadUploaderInstance) {
            __Messenger.loadUploaderInstance();
        }
        __Messenger.hideShowDropdownButtons(false);
        $('#lwSendMessageForm').show();
        $('#lwDeclineMessage').hide();
    }
}

var currentSelectedUserId = null,
    currentSelectedUserUid = null,
    optionalLoggedInUserId = null;
// After getting response from selected user
function userChatResponse(responseData) {
    $(".lw-messenger").unbind();
    $('#lwChatSidebarToggle').unbind();
    // $('#lwChatSidebarToggle').unbind();
    if (responseData.reaction == 1) {
        
        currentSelectedUserId = responseData.data.userData.user_id;
        currentSelectedUserUid = responseData.data.userData.user_uid;
        optionalUserId = responseData.data.userData.optionalLoggedInUserId;
        _.defer(function () {
            optionalLoggedInUserId = responseData.data.userData.optionalLoggedInUserId;
        });
        __Messenger.sendMessageUrl = __Utils.apiURL(__Messenger.sendMessageRawUrl, { userId: currentSelectedUserId });
        _.defer(function () {
            $(".lw-messenger-chat-window").scrollTop(1000000);
            var messageRequestStatus = responseData.data.userData.messageRequestStatus;
            handleMessageActionContainer(messageRequestStatus, true);
            __Messenger.hideShowChatSidebar();
            __Messenger.loadEmojiContent();
            __Messenger.openStickerBottomSheet();
            __Messenger.openGifBottomSheet();
            __Messenger.createSendButtonInstance();
            _.delay(function () {
                __Messenger.hideShowDropdownButtons(responseData.data.userData.enableAudioVideoLinks);
            }, 100);
            __Messenger.showMessageRequestNotification();
        });
    }
}
(function ($) {
    "use strict"; // Start of use strict

    __globals.translate_strings['uploader_default_text'] = "<span class='filepond--label-action'>Drag & Drop Files or Browse</span>";

    // Toggle the side navigation
    $("#sidebarToggle, #sidebarToggleTop").on('click tap', function (e) {
        $("body").toggleClass("sidebar-toggled");
        $(".sidebar").toggleClass("toggled");
        if ($(".sidebar").hasClass("toggled")) {
            $('.sidebar .collapse').collapse('hide');
        };
    });

    // Close any open menu accordions when window is resized below 768px
    $(window).resize(function () {
        if ($(window).width() < 768) {
            $('.sidebar .collapse').collapse('hide');
        };
    });

    if ($(window).width() < 768) {
        $("body").toggleClass("sidebar-toggled");
        $(".sidebar").toggleClass("toggled");
    };

    // Prevent the content wrapper from scrolling when the fixed side navigation hovered over
    $('body.fixed-nav .sidebar').on('mousewheel DOMMouseScroll wheel', function (e) {
        if ($(window).width() > 768) {
            var e0 = e.originalEvent,
                delta = e0.wheelDelta || -e0.detail;
            this.scrollTop += (delta < 0 ? 1 : -1) * 30;
            e.preventDefault();
        }
    });

    // Scroll to top button appear
    $(document).on('scroll', function () {
        var scrollDistance = $(this).scrollTop();
        if (scrollDistance > 100) {
            $('.scroll-to-top').fadeIn();
        } else {
            $('.scroll-to-top').fadeOut();
        }
    });

    // Smooth scrolling using jQuery easing
    $(document).on('click', 'a.scroll-to-top', function (e) {
        var $anchor = $(this);
        $('html, body').stop().animate({
            scrollTop: ($($anchor.attr('href')).offset().top)
        }, 1000, 'easeInOutExpo');
        e.preventDefault();
    });

})(jQuery); // End of use strict

// Use for filepond file uploader
var fileUploaderInit = function () {
    FilePond.registerPlugin(
        FilePondPluginImagePreview,
        FilePondPluginFilePoster,
        FilePondPluginFileValidateType
    );
    $('.lw-file-uploader').each(function (index, uploader) {
        var actionUrl = $(this).data('action'),
            responseCallback = $(this).data('callback'),
            defaultImage = $(this).data('default-image-url'),
            removeMediaAfterUpload = $(this).data('remove-media'),
            allowReplace = $(this).data('allow-replace'),
            allowRevert = $(this).data('allow-revert'),
            removeAllMediaAfterUpload = $(this).data('remove-all-media'),
            allowedMediaExtension = $(this).data('allowed-media'),
            filePondAdditionalOptions = {
                maxParallelUploads: 10,
                imagePreviewMaxHeight: 175,
                labelIdle: $(this).data('label-idle') ? $(this).data('label-idle') : __Utils.getTranslation('uploader_default_text'),
                acceptedFileTypes: allowedMediaExtension,
                fileValidateTypeDetectType: function (source, type) {
                    return new Promise(function (resolve, reject) {
                        if (allowedMediaExtension) {
                            if (allowedMediaExtension.indexOf(type) < 0) {
                                reject();
                            }
                        }
                        resolve(type);
                    })
                },
                allowRevert: allowRevert ? allowRevert : false,
                allowReplace: allowReplace ? allowReplace : false,
                credits:false,
                server: {
                    process: {
                        url: actionUrl,
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': appConfig.csrf_token
                        },
                        withCredentials: false,
                        onload: function (response) {
                            var requestData = JSON.parse(response);
                            // Show message when upload complete
                            switch (requestData.reaction) {
                                case 1:
                                    $('.lw-uploaded-file').val(requestData.data.fileName);
                                    showSuccessMessage(requestData.data.message);
                                    break;
                                case 14:
                                    showWarnMessage(requestData.data.message);
                                    break;
                                default:
                                    showErrorMessage(requestData.data.message);
                                    break;
                            }

                            var responseCallbackFn = window[responseCallback];
                            if (typeof responseCallbackFn === 'function') {
                                responseCallbackFn(requestData);
                            }
                        },
                    }
                },
                onprocessfile: function (error, file) {
                    if (removeMediaAfterUpload) {
                        pond.removeFile(file.id);
                    }
                    if (removeAllMediaAfterUpload) {
                        pond.removeFiles();
                    }
                }/* ,
                      onprocessfilerevert: function (file) {
                          __pr(error, file);
                      } */
            };

        if (typeof defaultImage != 'undefined' && !_.isEmpty(defaultImage)) {
            filePondAdditionalOptions = $.extend({}, filePondAdditionalOptions, {
                files: [
                    {
                        // set type to local to indicate an already uploaded file
                        options: {
                            type: 'local',
                            file: {
                                name: '',
                                size: uploader.size,
                                type: 'image/jpg'
                            },
                            // Pass Default Image Url
                            metadata: {
                                poster: defaultImage
                            }
                        }
                    }
                ]
            });
        }

        var pond = FilePond.create(this, filePondAdditionalOptions);
    });
};
fileUploaderInit();

var photoSwipeGallery = function (items, index) {

    //default index
    var index = parseInt(index);

    // default options
    var options = {
        index: index,
        history: false,
        focus: false,
        closeEl: true,
        captionEl: true,
        fullscreenEl: true,
        zoomEl: true,
        shareEl: false,
        counterEl: true,
        arrowEl: true,
        preloaderEl: true,
        tapToToggleControls: false,
        showAnimationDuration: 0,
        hideAnimationDuration: 0,
    };

    var gallery = new PhotoSwipe(document.querySelectorAll('.pswp')[0], PhotoSwipeUI_Default, items, options);
    gallery.init();

    // Gallery starts closing
    // Note: 05 JUN 2023 - as its creating an issue while closing on datatable we have fixed it using following trick
    if ($('body.lw-admin-section').length) {
        gallery.listen('destroy', function () {
            _.defer(function () {
                $('body.lw-admin-section .pswp').replaceWith(`<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="pswp__bg"></div>
        <div class="pswp__scroll-wrap">
            <div class="pswp__container">
                <div class="pswp__item"></div>
                <div class="pswp__item"></div>
                <div class="pswp__item"></div>
            </div>
            <div class="pswp__ui pswp__ui--hidden">
                <div class="pswp__top-bar">
                    <div class="pswp__counter"></div>
                    <button class="pswp__button pswp__button--close" title="Close (Esc)"></button>
                    <button class="pswp__button pswp__button--share" title="Share"></button>
                    <button class="pswp__button pswp__button--fs" title="Toggle fullscreen"></button>
                    <button class="pswp__button pswp__button--zoom" title="Zoom in/out"></button>
                    <div class="pswp__preloader">
                        <div class="pswp__preloader__icn">
                            <div class="pswp__preloader__cut">
                                <div class="pswp__preloader__donut"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
                    <div class="pswp__share-tooltip"></div>
                </div>
                <button class="pswp__button pswp__button--arrow--left" title="Previous (arrow left)">
                </button>
                <button class="pswp__button pswp__button--arrow--right" title="Next (arrow right)">
                </button>
                <div class="pswp__caption">
                    <div class="pswp__caption__center"></div>
                </div>
            </div>
        </div>
    </div>`);
            });
        });
    };
}

//for handling photoswipe gallery
$(function () {

    $('.lw-page-content').on('click', '.lw-datatable-photoswipe-gallery', function (event) {

        var items;
        var index = 0;

        if ($(event.target).hasClass('lw-photoswipe-gallery-img')) {
            // for fetching  all imgs url
            items = [{
                'src': $(event.target).attr('src'),
                'w': 900,
                'h': 900
            }];

            photoSwipeGallery(items, index);
        }
    });

    $('.lw-page-content').on('click', '.lw-photoswipe-gallery-img', function (event) {
        var siblings = $(this).siblings('.lw-photoswipe-gallery-img').addBack();
        var items;
        var index = 0;

        if (siblings.length > 0) {
            items = siblings.map(function (index, elem) {
                return {
                    'src': $(elem).attr('src'),
                    'w': 900,
                    'h': 900
                }
            });

            //if index is set
            if ($(event.target).data('img-index')) {
                index = $(event.target).data('img-index');
            }

            // if items not empty
            if (items.length > 0) {
                photoSwipeGallery(items, index);
            }
        } else {
            items = [{
                'src': $(event.target).attr('src'),
                'w': 900,
                'h': 900
            }];
            // if items not empty
            if (items.length > 0) {
                photoSwipeGallery(items, index);
            }
        }
    });
});

var applyLazyImages = function () {
    $(".lw-lazy-img").Lazy({
        // effect: "fadeIn",
        // effectTime: 200,
        // threshold: 0,
        beforeLoad: function ($element) {
            // called before an elements gets handled
            // $element.addClass('lw-lazy-img-loading');
        },
        afterLoad: function ($element) {
            // called after an element was successfully handled
            $element.addClass('lw-lazy-img-loaded');
            //    $element.removeClass('lw-lazy-img-loading');
        },
        onError: function ($element) {
            $element.addClass('lw-lazy-img-error');
            // $element.removeClass('lw-lazy-img-loading');
            console.log('error loading ' + $element.data('src'));
        }
    });
}

$(function () {
    applyLazyImages();
});

$(document).on('lw_events_ajax_success', function (e, options) {
    var response = options.response;
    if (_.get(response, 'data.auth_info.authorized') == false) {
        __Utils.viewReload();
    } else if ((response.data != '') && (_.isString(response.data) || (response.response_action && (response.response_action.target != '.lw-page-content')))) {
        $("#lwNextPageLink").remove();
        $("#lwLoadMoreContentContainer").append(response.data);
    }
    applyLazyImages();
});

// Find users load more event callback function
$(document).on('onLoadMoreFilterUsers', function (e, options) {
    var responseData = options.response,
        requestData = responseData.data,
            appendData = responseData.response_action.content;
        $('#lwUserFilterContainer').append(appendData);
        $('#lwLoadMoreButton').data('action', requestData.nextPageUrl);
        if (!requestData.hasMorePages) {
            $('.lw-load-more-container').hide();
            $('#lwLoadMoreResultMessage').show();
        }
        applyLazyImages();
});

/* $('.lw-public-master .sidebar li.nav-item').on('click', function (e) {
    $('.lw-public-master .sidebar .nav-item').removeClass('active');
    $(this).addClass('active');
}); */

/* $('.lw-admin-section .sidebar .nav-item:not(.lw-settings-sub-menu-items)').on('click', function (e) {
    $('.lw-admin-section .sidebar .nav-item').removeClass('active');
    $('.lw-admin-section .sidebar .nav-item .nav-link').removeClass('active');
    $(this).addClass('active');
}); */

$('.sidebar .nav-item .nav-link, .lw-public-master .sidebar li.nav-item').on('click', function (e) {
    $('.sidebar .nav-item, .sidebar .nav-item .nav-link').removeClass('active');
    $(this).parents('.nav-item.lw-settings-sub-menu-items').find('.nav-link').first().addClass('active');
    $(this).addClass('active');
});

var showConfirmation = function (containerId, yesCallback, options, confirmParams) {

    var $messageItem = (!_.includes(containerId, ' ')) ? $(containerId) : false,
        confirmationContainer = '';

    if ($messageItem && $messageItem.length) {
        confirmationContainer = _.template($messageItem.html());
    } else {
        confirmationContainer = containerId;
    }
    if (!options) {
        options = {};
    }

    options = _.assign({
        showCancelBtn: true,
        type: 'warning',
        confirmBtnColor: '#d33d33',
        cancelButtonText: 'Cancel',
        confirmButtonText: 'Yes',
        background: 'white',
        cancelBtnColor: '#6e7881',
        popup: '',
        html:'',
        title:'',
        showDenyButton: false,
        denyButtonText: "Don't save",
    }, options);

    if (!confirmParams) {
        confirmParams = {};
    }

    Swal.fire({
        // title: 'Are you sure?',
        title: options['title'],
        html: _.isString(confirmationContainer) ? confirmationContainer : confirmationContainer(confirmParams),
        icon: options['type'],
        confirmButtonText: options['confirmButtonText'],
        cancelButtonColor: options['cancelBtnColor'],
        cancelButtonText: options['cancelButtonText'],
        confirmButtonColor: options['confirmBtnColor'], // 3085d6
        showCancelButton: options['showCancelBtn'],
        background: options['background'],
        showDenyButton: options['showDenyButton'],
        denyButtonText: options['denyButtonText'],
        background: '#333',
        // popup: 'dark-theme',
        customClass: {
            popup: options['popup'],
        },
    }).then(function (result) {
        if (result.isConfirmed) {
            yesCallback();
        }
    });
};

window.getPageMetaData = function (item) {
    return $('script[data-'+item+']').data(item);
}

$(document).on('lwPrepareUploadPlugIn', function (e, options) {
    fileUploaderInit();
});

// as state is set programmatically for lw-ajax-action using the url change we needs to force back/forward button work.
$(window).on('popstate', function(event) {
    location.reload();
 });