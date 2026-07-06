var __defProp = Object.defineProperty;
var __defNormalProp = (obj, key, value) => key in obj ? __defProp(obj, key, { enumerable: true, configurable: true, writable: true, value }) : obj[key] = value;
var __publicField = (obj, key, value) => __defNormalProp(obj, typeof key !== "symbol" ? key + "" : key, value);
var e, t, s, a;
!(function(e2) {
  e2.POST_MESSAGE = "postMessage";
})(e || (e = {})), (function(e2) {
  e2.SDK_ANGIE_READY_PING = "sdk-angie-ready-ping", e2.SDK_REQUEST_CLIENT_CREATION = "sdk-request-client-creation", e2.SDK_REQUEST_INIT_SERVER = "sdk-request-init-server";
})(t || (t = {}));
class r {
  constructor() {
    __publicField(this, "isAngieReady", false);
    __publicField(this, "readyPromise");
    __publicField(this, "readyResolve");
    if (this.readyPromise = new Promise(((e3) => {
      this.readyResolve = e3;
    })), "undefined" == typeof window) return;
    let e2 = 0;
    const s2 = () => {
      if (this.isAngieReady || e2 >= 500) return void (!this.isAngieReady && e2 >= 500 && this.handleDetectionTimeout());
      const a2 = new MessageChannel();
      a2.port1.onmessage = (e3) => {
        this.handleAngieReady(e3.data), a2.port1.close(), a2.port2.close();
      };
      const r2 = { type: t.SDK_ANGIE_READY_PING, timestamp: Date.now() };
      window.postMessage(r2, window.location.origin, [a2.port2]), e2++, setTimeout(s2, 500);
    };
    s2();
  }
  handleAngieReady(e2) {
    this.isAngieReady = true;
    const t2 = { isReady: true, version: e2.version, capabilities: e2.capabilities };
    this.readyResolve && this.readyResolve(t2);
  }
  handleDetectionTimeout() {
    this.readyResolve && this.readyResolve({ isReady: false }), console.warn("AngieMcpSdk: AngieDetector: Detection timeout - Angie may not be available");
  }
  isReady() {
    return this.isAngieReady;
  }
  async waitForReady() {
    return this.readyPromise;
  }
}
class n {
  constructor() {
    __publicField(this, "queue", []);
    __publicField(this, "isProcessing", false);
  }
  add(e2) {
    const t2 = { id: this.generateId(e2), config: e2, timestamp: Date.now(), status: "pending" };
    return this.queue.push(t2), console.log(`RegistrationQueue: Added server "${e2.name}" to queue`), t2;
  }
  getAll() {
    return [...this.queue];
  }
  getPending() {
    return this.queue.filter(((e2) => "pending" === e2.status));
  }
  updateStatus(e2, t2, s2) {
    const a2 = this.queue.find(((t3) => t3.id === e2));
    a2 && (a2.status = t2, s2 && (a2.error = s2), console.log(`RegistrationQueue: Updated server ${e2} status to ${t2}`));
  }
  async processQueue(e2) {
    if (this.isProcessing) return void console.log("RegistrationQueue: Already processing queue");
    this.isProcessing = true;
    const t2 = this.getPending();
    console.log(`RegistrationQueue: Processing ${t2.length} pending registrations`);
    try {
      for (const s2 of t2) try {
        await e2(s2), this.updateStatus(s2.id, "registered");
      } catch (e3) {
        const t3 = e3 instanceof Error ? e3.message : String(e3);
        this.updateStatus(s2.id, "failed", t3), console.error(`RegistrationQueue: Failed to process registration ${s2.id}:`, t3);
      }
    } finally {
      this.isProcessing = false;
    }
  }
  clear() {
    this.queue = [], console.log("RegistrationQueue: Cleared all registrations");
  }
  remove(e2) {
    const t2 = this.queue.findIndex(((t3) => t3.id === e2));
    return -1 !== t2 && (this.queue.splice(t2, 1), console.log(`RegistrationQueue: Removed registration ${e2}`), true);
  }
  generateId(e2) {
    return `reg_${e2.name}_${e2.version}_${Date.now()}`;
  }
}
class i {
  async requestClientCreation(s2) {
    const { config: a2 } = s2, r2 = { serverId: s2.id, serverName: a2.name, serverVersion: a2.version, description: a2.description, transport: e.POST_MESSAGE, capabilities: a2.capabilities };
    return new Promise(((e2, s3) => {
      const a3 = new MessageChannel(), n2 = setTimeout((() => {
        s3(new Error("Client creation request timed out after 10000ms"));
      }), 1e4);
      a3.port1.onmessage = (t2) => {
        clearTimeout(n2), e2(t2.data);
      };
      const i2 = { type: t.SDK_REQUEST_CLIENT_CREATION, payload: r2, timestamp: Date.now() };
      window.postMessage(i2, window.location.origin, [a3.port2]);
    }));
  }
}
!(function(e2) {
  e2.assertEqual = (e3) => {
  }, e2.assertIs = function(e3) {
  }, e2.assertNever = function(e3) {
    throw new Error();
  }, e2.arrayToEnum = (e3) => {
    const t2 = {};
    for (const s2 of e3) t2[s2] = s2;
    return t2;
  }, e2.getValidEnumValues = (t2) => {
    const s2 = e2.objectKeys(t2).filter(((e3) => "number" != typeof t2[t2[e3]])), a2 = {};
    for (const e3 of s2) a2[e3] = t2[e3];
    return e2.objectValues(a2);
  }, e2.objectValues = (t2) => e2.objectKeys(t2).map((function(e3) {
    return t2[e3];
  })), e2.objectKeys = "function" == typeof Object.keys ? (e3) => Object.keys(e3) : (e3) => {
    const t2 = [];
    for (const s2 in e3) Object.prototype.hasOwnProperty.call(e3, s2) && t2.push(s2);
    return t2;
  }, e2.find = (e3, t2) => {
    for (const s2 of e3) if (t2(s2)) return s2;
  }, e2.isInteger = "function" == typeof Number.isInteger ? (e3) => Number.isInteger(e3) : (e3) => "number" == typeof e3 && Number.isFinite(e3) && Math.floor(e3) === e3, e2.joinValues = function(e3, t2 = " | ") {
    return e3.map(((e4) => "string" == typeof e4 ? `'${e4}'` : e4)).join(t2);
  }, e2.jsonStringifyReplacer = (e3, t2) => "bigint" == typeof t2 ? t2.toString() : t2;
})(s || (s = {})), (function(e2) {
  e2.mergeShapes = (e3, t2) => ({ ...e3, ...t2 });
})(a || (a = {}));
const o = s.arrayToEnum(["string", "nan", "number", "integer", "float", "boolean", "date", "bigint", "symbol", "function", "undefined", "null", "array", "object", "unknown", "promise", "void", "never", "map", "set"]), d = (e2) => {
  switch (typeof e2) {
    case "undefined":
      return o.undefined;
    case "string":
      return o.string;
    case "number":
      return Number.isNaN(e2) ? o.nan : o.number;
    case "boolean":
      return o.boolean;
    case "function":
      return o.function;
    case "bigint":
      return o.bigint;
    case "symbol":
      return o.symbol;
    case "object":
      return Array.isArray(e2) ? o.array : null === e2 ? o.null : e2.then && "function" == typeof e2.then && e2.catch && "function" == typeof e2.catch ? o.promise : "undefined" != typeof Map && e2 instanceof Map ? o.map : "undefined" != typeof Set && e2 instanceof Set ? o.set : "undefined" != typeof Date && e2 instanceof Date ? o.date : o.object;
    default:
      return o.unknown;
  }
}, c = s.arrayToEnum(["invalid_type", "invalid_literal", "custom", "invalid_union", "invalid_union_discriminator", "invalid_enum_value", "unrecognized_keys", "invalid_arguments", "invalid_return_type", "invalid_date", "invalid_string", "too_small", "too_big", "invalid_intersection_types", "not_multiple_of", "not_finite"]);
class u extends Error {
  get errors() {
    return this.issues;
  }
  constructor(e2) {
    super(), this.issues = [], this.addIssue = (e3) => {
      this.issues = [...this.issues, e3];
    }, this.addIssues = (e3 = []) => {
      this.issues = [...this.issues, ...e3];
    };
    const t2 = new.target.prototype;
    Object.setPrototypeOf ? Object.setPrototypeOf(this, t2) : this.__proto__ = t2, this.name = "ZodError", this.issues = e2;
  }
  format(e2) {
    const t2 = e2 || function(e3) {
      return e3.message;
    }, s2 = { _errors: [] }, a2 = (e3) => {
      for (const r2 of e3.issues) if ("invalid_union" === r2.code) r2.unionErrors.map(a2);
      else if ("invalid_return_type" === r2.code) a2(r2.returnTypeError);
      else if ("invalid_arguments" === r2.code) a2(r2.argumentsError);
      else if (0 === r2.path.length) s2._errors.push(t2(r2));
      else {
        let e4 = s2, a3 = 0;
        for (; a3 < r2.path.length; ) {
          const s3 = r2.path[a3];
          a3 === r2.path.length - 1 ? (e4[s3] = e4[s3] || { _errors: [] }, e4[s3]._errors.push(t2(r2))) : e4[s3] = e4[s3] || { _errors: [] }, e4 = e4[s3], a3++;
        }
      }
    };
    return a2(this), s2;
  }
  static assert(e2) {
    if (!(e2 instanceof u)) throw new Error(`Not a ZodError: ${e2}`);
  }
  toString() {
    return this.message;
  }
  get message() {
    return JSON.stringify(this.issues, s.jsonStringifyReplacer, 2);
  }
  get isEmpty() {
    return 0 === this.issues.length;
  }
  flatten(e2 = (e3) => e3.message) {
    const t2 = {}, s2 = [];
    for (const a2 of this.issues) a2.path.length > 0 ? (t2[a2.path[0]] = t2[a2.path[0]] || [], t2[a2.path[0]].push(e2(a2))) : s2.push(e2(a2));
    return { formErrors: s2, fieldErrors: t2 };
  }
  get formErrors() {
    return this.flatten();
  }
}
u.create = (e2) => new u(e2);
const l = (e2, t2) => {
  let a2;
  switch (e2.code) {
    case c.invalid_type:
      a2 = e2.received === o.undefined ? "Required" : `Expected ${e2.expected}, received ${e2.received}`;
      break;
    case c.invalid_literal:
      a2 = `Invalid literal value, expected ${JSON.stringify(e2.expected, s.jsonStringifyReplacer)}`;
      break;
    case c.unrecognized_keys:
      a2 = `Unrecognized key(s) in object: ${s.joinValues(e2.keys, ", ")}`;
      break;
    case c.invalid_union:
      a2 = "Invalid input";
      break;
    case c.invalid_union_discriminator:
      a2 = `Invalid discriminator value. Expected ${s.joinValues(e2.options)}`;
      break;
    case c.invalid_enum_value:
      a2 = `Invalid enum value. Expected ${s.joinValues(e2.options)}, received '${e2.received}'`;
      break;
    case c.invalid_arguments:
      a2 = "Invalid function arguments";
      break;
    case c.invalid_return_type:
      a2 = "Invalid function return type";
      break;
    case c.invalid_date:
      a2 = "Invalid date";
      break;
    case c.invalid_string:
      "object" == typeof e2.validation ? "includes" in e2.validation ? (a2 = `Invalid input: must include "${e2.validation.includes}"`, "number" == typeof e2.validation.position && (a2 = `${a2} at one or more positions greater than or equal to ${e2.validation.position}`)) : "startsWith" in e2.validation ? a2 = `Invalid input: must start with "${e2.validation.startsWith}"` : "endsWith" in e2.validation ? a2 = `Invalid input: must end with "${e2.validation.endsWith}"` : s.assertNever(e2.validation) : a2 = "regex" !== e2.validation ? `Invalid ${e2.validation}` : "Invalid";
      break;
    case c.too_small:
      a2 = "array" === e2.type ? `Array must contain ${e2.exact ? "exactly" : e2.inclusive ? "at least" : "more than"} ${e2.minimum} element(s)` : "string" === e2.type ? `String must contain ${e2.exact ? "exactly" : e2.inclusive ? "at least" : "over"} ${e2.minimum} character(s)` : "number" === e2.type ? `Number must be ${e2.exact ? "exactly equal to " : e2.inclusive ? "greater than or equal to " : "greater than "}${e2.minimum}` : "date" === e2.type ? `Date must be ${e2.exact ? "exactly equal to " : e2.inclusive ? "greater than or equal to " : "greater than "}${new Date(Number(e2.minimum))}` : "Invalid input";
      break;
    case c.too_big:
      a2 = "array" === e2.type ? `Array must contain ${e2.exact ? "exactly" : e2.inclusive ? "at most" : "less than"} ${e2.maximum} element(s)` : "string" === e2.type ? `String must contain ${e2.exact ? "exactly" : e2.inclusive ? "at most" : "under"} ${e2.maximum} character(s)` : "number" === e2.type ? `Number must be ${e2.exact ? "exactly" : e2.inclusive ? "less than or equal to" : "less than"} ${e2.maximum}` : "bigint" === e2.type ? `BigInt must be ${e2.exact ? "exactly" : e2.inclusive ? "less than or equal to" : "less than"} ${e2.maximum}` : "date" === e2.type ? `Date must be ${e2.exact ? "exactly" : e2.inclusive ? "smaller than or equal to" : "smaller than"} ${new Date(Number(e2.maximum))}` : "Invalid input";
      break;
    case c.custom:
      a2 = "Invalid input";
      break;
    case c.invalid_intersection_types:
      a2 = "Intersection results could not be merged";
      break;
    case c.not_multiple_of:
      a2 = `Number must be a multiple of ${e2.multipleOf}`;
      break;
    case c.not_finite:
      a2 = "Number must be finite";
      break;
    default:
      a2 = t2.defaultError, s.assertNever(e2);
  }
  return { message: a2 };
};
let h = l;
function p(e2, t2) {
  const s2 = h, a2 = ((e3) => {
    const { data: t3, path: s3, errorMaps: a3, issueData: r2 } = e3, n2 = [...s3, ...r2.path || []], i2 = { ...r2, path: n2 };
    if (void 0 !== r2.message) return { ...r2, path: n2, message: r2.message };
    let o2 = "";
    const d2 = a3.filter(((e4) => !!e4)).slice().reverse();
    for (const e4 of d2) o2 = e4(i2, { data: t3, defaultError: o2 }).message;
    return { ...r2, path: n2, message: o2 };
  })({ issueData: t2, data: e2.data, path: e2.path, errorMaps: [e2.common.contextualErrorMap, e2.schemaErrorMap, s2, s2 === l ? void 0 : l].filter(((e3) => !!e3)) });
  e2.common.issues.push(a2);
}
class m {
  constructor() {
    this.value = "valid";
  }
  dirty() {
    "valid" === this.value && (this.value = "dirty");
  }
  abort() {
    "aborted" !== this.value && (this.value = "aborted");
  }
  static mergeArray(e2, t2) {
    const s2 = [];
    for (const a2 of t2) {
      if ("aborted" === a2.status) return g;
      "dirty" === a2.status && e2.dirty(), s2.push(a2.value);
    }
    return { status: e2.value, value: s2 };
  }
  static async mergeObjectAsync(e2, t2) {
    const s2 = [];
    for (const e3 of t2) {
      const t3 = await e3.key, a2 = await e3.value;
      s2.push({ key: t3, value: a2 });
    }
    return m.mergeObjectSync(e2, s2);
  }
  static mergeObjectSync(e2, t2) {
    const s2 = {};
    for (const a2 of t2) {
      const { key: t3, value: r2 } = a2;
      if ("aborted" === t3.status) return g;
      if ("aborted" === r2.status) return g;
      "dirty" === t3.status && e2.dirty(), "dirty" === r2.status && e2.dirty(), "__proto__" === t3.value || void 0 === r2.value && !a2.alwaysSet || (s2[t3.value] = r2.value);
    }
    return { status: e2.value, value: s2 };
  }
}
const g = Object.freeze({ status: "aborted" }), f = (e2) => ({ status: "dirty", value: e2 }), y = (e2) => ({ status: "valid", value: e2 }), _ = (e2) => "aborted" === e2.status, v = (e2) => "dirty" === e2.status, x = (e2) => "valid" === e2.status, k = (e2) => "undefined" != typeof Promise && e2 instanceof Promise;
var b;
!(function(e2) {
  e2.errToObj = (e3) => "string" == typeof e3 ? { message: e3 } : e3 || {}, e2.toString = (e3) => "string" == typeof e3 ? e3 : e3 == null ? void 0 : e3.message;
})(b || (b = {}));
class w {
  constructor(e2, t2, s2, a2) {
    this._cachedPath = [], this.parent = e2, this.data = t2, this._path = s2, this._key = a2;
  }
  get path() {
    return this._cachedPath.length || (Array.isArray(this._key) ? this._cachedPath.push(...this._path, ...this._key) : this._cachedPath.push(...this._path, this._key)), this._cachedPath;
  }
}
const S = (e2, t2) => {
  if (x(t2)) return { success: true, data: t2.value };
  if (!e2.common.issues.length) throw new Error("Validation failed but no issues detected.");
  return { success: false, get error() {
    if (this._error) return this._error;
    const t3 = new u(e2.common.issues);
    return this._error = t3, this._error;
  } };
};
function T(e2) {
  if (!e2) return {};
  const { errorMap: t2, invalid_type_error: s2, required_error: a2, description: r2 } = e2;
  if (t2 && (s2 || a2)) throw new Error(`Can't use "invalid_type_error" or "required_error" in conjunction with custom error map.`);
  return t2 ? { errorMap: t2, description: r2 } : { errorMap: (t3, r3) => {
    const { message: n2 } = e2;
    return "invalid_enum_value" === t3.code ? { message: n2 ?? r3.defaultError } : void 0 === r3.data ? { message: n2 ?? a2 ?? r3.defaultError } : "invalid_type" !== t3.code ? { message: r3.defaultError } : { message: n2 ?? s2 ?? r3.defaultError };
  }, description: r2 };
}
class A {
  get description() {
    return this._def.description;
  }
  _getType(e2) {
    return d(e2.data);
  }
  _getOrReturnCtx(e2, t2) {
    return t2 || { common: e2.parent.common, data: e2.data, parsedType: d(e2.data), schemaErrorMap: this._def.errorMap, path: e2.path, parent: e2.parent };
  }
  _processInputParams(e2) {
    return { status: new m(), ctx: { common: e2.parent.common, data: e2.data, parsedType: d(e2.data), schemaErrorMap: this._def.errorMap, path: e2.path, parent: e2.parent } };
  }
  _parseSync(e2) {
    const t2 = this._parse(e2);
    if (k(t2)) throw new Error("Synchronous parse encountered promise.");
    return t2;
  }
  _parseAsync(e2) {
    const t2 = this._parse(e2);
    return Promise.resolve(t2);
  }
  parse(e2, t2) {
    const s2 = this.safeParse(e2, t2);
    if (s2.success) return s2.data;
    throw s2.error;
  }
  safeParse(e2, t2) {
    const s2 = { common: { issues: [], async: (t2 == null ? void 0 : t2.async) ?? false, contextualErrorMap: t2 == null ? void 0 : t2.errorMap }, path: (t2 == null ? void 0 : t2.path) || [], schemaErrorMap: this._def.errorMap, parent: null, data: e2, parsedType: d(e2) }, a2 = this._parseSync({ data: e2, path: s2.path, parent: s2 });
    return S(s2, a2);
  }
  "~validate"(e2) {
    var _a, _b;
    const t2 = { common: { issues: [], async: !!this["~standard"].async }, path: [], schemaErrorMap: this._def.errorMap, parent: null, data: e2, parsedType: d(e2) };
    if (!this["~standard"].async) try {
      const s2 = this._parseSync({ data: e2, path: [], parent: t2 });
      return x(s2) ? { value: s2.value } : { issues: t2.common.issues };
    } catch (e3) {
      ((_b = (_a = e3 == null ? void 0 : e3.message) == null ? void 0 : _a.toLowerCase()) == null ? void 0 : _b.includes("encountered")) && (this["~standard"].async = true), t2.common = { issues: [], async: true };
    }
    return this._parseAsync({ data: e2, path: [], parent: t2 }).then(((e3) => x(e3) ? { value: e3.value } : { issues: t2.common.issues }));
  }
  async parseAsync(e2, t2) {
    const s2 = await this.safeParseAsync(e2, t2);
    if (s2.success) return s2.data;
    throw s2.error;
  }
  async safeParseAsync(e2, t2) {
    const s2 = { common: { issues: [], contextualErrorMap: t2 == null ? void 0 : t2.errorMap, async: true }, path: (t2 == null ? void 0 : t2.path) || [], schemaErrorMap: this._def.errorMap, parent: null, data: e2, parsedType: d(e2) }, a2 = this._parse({ data: e2, path: s2.path, parent: s2 }), r2 = await (k(a2) ? a2 : Promise.resolve(a2));
    return S(s2, r2);
  }
  refine(e2, t2) {
    const s2 = (e3) => "string" == typeof t2 || void 0 === t2 ? { message: t2 } : "function" == typeof t2 ? t2(e3) : t2;
    return this._refinement(((t3, a2) => {
      const r2 = e2(t3), n2 = () => a2.addIssue({ code: c.custom, ...s2(t3) });
      return "undefined" != typeof Promise && r2 instanceof Promise ? r2.then(((e3) => !!e3 || (n2(), false))) : !!r2 || (n2(), false);
    }));
  }
  refinement(e2, t2) {
    return this._refinement(((s2, a2) => !!e2(s2) || (a2.addIssue("function" == typeof t2 ? t2(s2, a2) : t2), false)));
  }
  _refinement(e2) {
    return new Te({ schema: this, typeName: je.ZodEffects, effect: { type: "refinement", refinement: e2 } });
  }
  superRefine(e2) {
    return this._refinement(e2);
  }
  constructor(e2) {
    this.spa = this.safeParseAsync, this._def = e2, this.parse = this.parse.bind(this), this.safeParse = this.safeParse.bind(this), this.parseAsync = this.parseAsync.bind(this), this.safeParseAsync = this.safeParseAsync.bind(this), this.spa = this.spa.bind(this), this.refine = this.refine.bind(this), this.refinement = this.refinement.bind(this), this.superRefine = this.superRefine.bind(this), this.optional = this.optional.bind(this), this.nullable = this.nullable.bind(this), this.nullish = this.nullish.bind(this), this.array = this.array.bind(this), this.promise = this.promise.bind(this), this.or = this.or.bind(this), this.and = this.and.bind(this), this.transform = this.transform.bind(this), this.brand = this.brand.bind(this), this.default = this.default.bind(this), this.catch = this.catch.bind(this), this.describe = this.describe.bind(this), this.pipe = this.pipe.bind(this), this.readonly = this.readonly.bind(this), this.isNullable = this.isNullable.bind(this), this.isOptional = this.isOptional.bind(this), this["~standard"] = { version: 1, vendor: "zod", validate: (e3) => this["~validate"](e3) };
  }
  optional() {
    return Ae.create(this, this._def);
  }
  nullable() {
    return Ce.create(this, this._def);
  }
  nullish() {
    return this.nullable().optional();
  }
  array() {
    return oe.create(this);
  }
  promise() {
    return Se.create(this, this._def);
  }
  or(e2) {
    return ue.create([this, e2], this._def);
  }
  and(e2) {
    return me.create(this, e2, this._def);
  }
  transform(e2) {
    return new Te({ ...T(this._def), schema: this, typeName: je.ZodEffects, effect: { type: "transform", transform: e2 } });
  }
  default(e2) {
    const t2 = "function" == typeof e2 ? e2 : () => e2;
    return new Re({ ...T(this._def), innerType: this, defaultValue: t2, typeName: je.ZodDefault });
  }
  brand() {
    return new Ie({ typeName: je.ZodBranded, type: this, ...T(this._def) });
  }
  catch(e2) {
    const t2 = "function" == typeof e2 ? e2 : () => e2;
    return new Ze({ ...T(this._def), innerType: this, catchValue: t2, typeName: je.ZodCatch });
  }
  describe(e2) {
    return new this.constructor({ ...this._def, description: e2 });
  }
  pipe(e2) {
    return Ee.create(this, e2);
  }
  readonly() {
    return Ne.create(this);
  }
  isOptional() {
    return this.safeParse(void 0).success;
  }
  isNullable() {
    return this.safeParse(null).success;
  }
}
const C = /^c[^\s-]{8,}$/i, R = /^[0-9a-z]+$/, Z = /^[0-9A-HJKMNP-TV-Z]{26}$/i, O = /^[0-9a-fA-F]{8}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{12}$/i, I = /^[a-z0-9_-]{21}$/i, E = /^[A-Za-z0-9-_]+\.[A-Za-z0-9-_]+\.[A-Za-z0-9-_]*$/, N = /^[-+]?P(?!$)(?:(?:[-+]?\d+Y)|(?:[-+]?\d+[.,]\d+Y$))?(?:(?:[-+]?\d+M)|(?:[-+]?\d+[.,]\d+M$))?(?:(?:[-+]?\d+W)|(?:[-+]?\d+[.,]\d+W$))?(?:(?:[-+]?\d+D)|(?:[-+]?\d+[.,]\d+D$))?(?:T(?=[\d+-])(?:(?:[-+]?\d+H)|(?:[-+]?\d+[.,]\d+H$))?(?:(?:[-+]?\d+M)|(?:[-+]?\d+[.,]\d+M$))?(?:[-+]?\d+(?:[.,]\d+)?S)?)??$/, j = /^(?!\.)(?!.*\.\.)([A-Z0-9_'+\-\.]*)[A-Z0-9_+-]@([A-Z0-9][A-Z0-9\-]*\.)+[A-Z]{2,}$/i;
let P;
const $ = /^(?:(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\.){3}(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])$/, M = /^(?:(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\.){3}(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\/(3[0-2]|[12]?[0-9])$/, D = /^(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]))$/, F = /^(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]))\/(12[0-8]|1[01][0-9]|[1-9]?[0-9])$/, q = /^([0-9a-zA-Z+/]{4})*(([0-9a-zA-Z+/]{2}==)|([0-9a-zA-Z+/]{3}=))?$/, z = /^([0-9a-zA-Z-_]{4})*(([0-9a-zA-Z-_]{2}(==)?)|([0-9a-zA-Z-_]{3}(=)?))?$/, L = "((\\d\\d[2468][048]|\\d\\d[13579][26]|\\d\\d0[48]|[02468][048]00|[13579][26]00)-02-29|\\d{4}-((0[13578]|1[02])-(0[1-9]|[12]\\d|3[01])|(0[469]|11)-(0[1-9]|[12]\\d|30)|(02)-(0[1-9]|1\\d|2[0-8])))", V = new RegExp(`^${L}$`);
function U(e2) {
  let t2 = "[0-5]\\d";
  return e2.precision ? t2 = `${t2}\\.\\d{${e2.precision}}` : null == e2.precision && (t2 = `${t2}(\\.\\d+)?`), `([01]\\d|2[0-3]):[0-5]\\d(:${t2})${e2.precision ? "+" : "?"}`;
}
function K(e2) {
  let t2 = `${L}T${U(e2)}`;
  const s2 = [];
  return s2.push(e2.local ? "Z?" : "Z"), e2.offset && s2.push("([+-]\\d{2}:?\\d{2})"), t2 = `${t2}(${s2.join("|")})`, new RegExp(`^${t2}$`);
}
function Q(e2, t2) {
  if (!E.test(e2)) return false;
  try {
    const [s2] = e2.split("."), a2 = s2.replace(/-/g, "+").replace(/_/g, "/").padEnd(s2.length + (4 - s2.length % 4) % 4, "="), r2 = JSON.parse(atob(a2));
    return !("object" != typeof r2 || null === r2 || "typ" in r2 && "JWT" !== (r2 == null ? void 0 : r2.typ) || !r2.alg || t2 && r2.alg !== t2);
  } catch {
    return false;
  }
}
function B(e2, t2) {
  return !("v4" !== t2 && t2 || !M.test(e2)) || !("v6" !== t2 && t2 || !F.test(e2));
}
class W extends A {
  _parse(e2) {
    if (this._def.coerce && (e2.data = String(e2.data)), this._getType(e2) !== o.string) {
      const t3 = this._getOrReturnCtx(e2);
      return p(t3, { code: c.invalid_type, expected: o.string, received: t3.parsedType }), g;
    }
    const t2 = new m();
    let a2;
    for (const i2 of this._def.checks) if ("min" === i2.kind) e2.data.length < i2.value && (a2 = this._getOrReturnCtx(e2, a2), p(a2, { code: c.too_small, minimum: i2.value, type: "string", inclusive: true, exact: false, message: i2.message }), t2.dirty());
    else if ("max" === i2.kind) e2.data.length > i2.value && (a2 = this._getOrReturnCtx(e2, a2), p(a2, { code: c.too_big, maximum: i2.value, type: "string", inclusive: true, exact: false, message: i2.message }), t2.dirty());
    else if ("length" === i2.kind) {
      const s2 = e2.data.length > i2.value, r3 = e2.data.length < i2.value;
      (s2 || r3) && (a2 = this._getOrReturnCtx(e2, a2), s2 ? p(a2, { code: c.too_big, maximum: i2.value, type: "string", inclusive: true, exact: true, message: i2.message }) : r3 && p(a2, { code: c.too_small, minimum: i2.value, type: "string", inclusive: true, exact: true, message: i2.message }), t2.dirty());
    } else if ("email" === i2.kind) j.test(e2.data) || (a2 = this._getOrReturnCtx(e2, a2), p(a2, { validation: "email", code: c.invalid_string, message: i2.message }), t2.dirty());
    else if ("emoji" === i2.kind) P || (P = new RegExp("^(\\p{Extended_Pictographic}|\\p{Emoji_Component})+$", "u")), P.test(e2.data) || (a2 = this._getOrReturnCtx(e2, a2), p(a2, { validation: "emoji", code: c.invalid_string, message: i2.message }), t2.dirty());
    else if ("uuid" === i2.kind) O.test(e2.data) || (a2 = this._getOrReturnCtx(e2, a2), p(a2, { validation: "uuid", code: c.invalid_string, message: i2.message }), t2.dirty());
    else if ("nanoid" === i2.kind) I.test(e2.data) || (a2 = this._getOrReturnCtx(e2, a2), p(a2, { validation: "nanoid", code: c.invalid_string, message: i2.message }), t2.dirty());
    else if ("cuid" === i2.kind) C.test(e2.data) || (a2 = this._getOrReturnCtx(e2, a2), p(a2, { validation: "cuid", code: c.invalid_string, message: i2.message }), t2.dirty());
    else if ("cuid2" === i2.kind) R.test(e2.data) || (a2 = this._getOrReturnCtx(e2, a2), p(a2, { validation: "cuid2", code: c.invalid_string, message: i2.message }), t2.dirty());
    else if ("ulid" === i2.kind) Z.test(e2.data) || (a2 = this._getOrReturnCtx(e2, a2), p(a2, { validation: "ulid", code: c.invalid_string, message: i2.message }), t2.dirty());
    else if ("url" === i2.kind) try {
      new URL(e2.data);
    } catch {
      a2 = this._getOrReturnCtx(e2, a2), p(a2, { validation: "url", code: c.invalid_string, message: i2.message }), t2.dirty();
    }
    else "regex" === i2.kind ? (i2.regex.lastIndex = 0, i2.regex.test(e2.data) || (a2 = this._getOrReturnCtx(e2, a2), p(a2, { validation: "regex", code: c.invalid_string, message: i2.message }), t2.dirty())) : "trim" === i2.kind ? e2.data = e2.data.trim() : "includes" === i2.kind ? e2.data.includes(i2.value, i2.position) || (a2 = this._getOrReturnCtx(e2, a2), p(a2, { code: c.invalid_string, validation: { includes: i2.value, position: i2.position }, message: i2.message }), t2.dirty()) : "toLowerCase" === i2.kind ? e2.data = e2.data.toLowerCase() : "toUpperCase" === i2.kind ? e2.data = e2.data.toUpperCase() : "startsWith" === i2.kind ? e2.data.startsWith(i2.value) || (a2 = this._getOrReturnCtx(e2, a2), p(a2, { code: c.invalid_string, validation: { startsWith: i2.value }, message: i2.message }), t2.dirty()) : "endsWith" === i2.kind ? e2.data.endsWith(i2.value) || (a2 = this._getOrReturnCtx(e2, a2), p(a2, { code: c.invalid_string, validation: { endsWith: i2.value }, message: i2.message }), t2.dirty()) : "datetime" === i2.kind ? K(i2).test(e2.data) || (a2 = this._getOrReturnCtx(e2, a2), p(a2, { code: c.invalid_string, validation: "datetime", message: i2.message }), t2.dirty()) : "date" === i2.kind ? V.test(e2.data) || (a2 = this._getOrReturnCtx(e2, a2), p(a2, { code: c.invalid_string, validation: "date", message: i2.message }), t2.dirty()) : "time" === i2.kind ? new RegExp(`^${U(i2)}$`).test(e2.data) || (a2 = this._getOrReturnCtx(e2, a2), p(a2, { code: c.invalid_string, validation: "time", message: i2.message }), t2.dirty()) : "duration" === i2.kind ? N.test(e2.data) || (a2 = this._getOrReturnCtx(e2, a2), p(a2, { validation: "duration", code: c.invalid_string, message: i2.message }), t2.dirty()) : "ip" === i2.kind ? (r2 = e2.data, ("v4" !== (n2 = i2.version) && n2 || !$.test(r2)) && ("v6" !== n2 && n2 || !D.test(r2)) && (a2 = this._getOrReturnCtx(e2, a2), p(a2, { validation: "ip", code: c.invalid_string, message: i2.message }), t2.dirty())) : "jwt" === i2.kind ? Q(e2.data, i2.alg) || (a2 = this._getOrReturnCtx(e2, a2), p(a2, { validation: "jwt", code: c.invalid_string, message: i2.message }), t2.dirty()) : "cidr" === i2.kind ? B(e2.data, i2.version) || (a2 = this._getOrReturnCtx(e2, a2), p(a2, { validation: "cidr", code: c.invalid_string, message: i2.message }), t2.dirty()) : "base64" === i2.kind ? q.test(e2.data) || (a2 = this._getOrReturnCtx(e2, a2), p(a2, { validation: "base64", code: c.invalid_string, message: i2.message }), t2.dirty()) : "base64url" === i2.kind ? z.test(e2.data) || (a2 = this._getOrReturnCtx(e2, a2), p(a2, { validation: "base64url", code: c.invalid_string, message: i2.message }), t2.dirty()) : s.assertNever(i2);
    var r2, n2;
    return { status: t2.value, value: e2.data };
  }
  _regex(e2, t2, s2) {
    return this.refinement(((t3) => e2.test(t3)), { validation: t2, code: c.invalid_string, ...b.errToObj(s2) });
  }
  _addCheck(e2) {
    return new W({ ...this._def, checks: [...this._def.checks, e2] });
  }
  email(e2) {
    return this._addCheck({ kind: "email", ...b.errToObj(e2) });
  }
  url(e2) {
    return this._addCheck({ kind: "url", ...b.errToObj(e2) });
  }
  emoji(e2) {
    return this._addCheck({ kind: "emoji", ...b.errToObj(e2) });
  }
  uuid(e2) {
    return this._addCheck({ kind: "uuid", ...b.errToObj(e2) });
  }
  nanoid(e2) {
    return this._addCheck({ kind: "nanoid", ...b.errToObj(e2) });
  }
  cuid(e2) {
    return this._addCheck({ kind: "cuid", ...b.errToObj(e2) });
  }
  cuid2(e2) {
    return this._addCheck({ kind: "cuid2", ...b.errToObj(e2) });
  }
  ulid(e2) {
    return this._addCheck({ kind: "ulid", ...b.errToObj(e2) });
  }
  base64(e2) {
    return this._addCheck({ kind: "base64", ...b.errToObj(e2) });
  }
  base64url(e2) {
    return this._addCheck({ kind: "base64url", ...b.errToObj(e2) });
  }
  jwt(e2) {
    return this._addCheck({ kind: "jwt", ...b.errToObj(e2) });
  }
  ip(e2) {
    return this._addCheck({ kind: "ip", ...b.errToObj(e2) });
  }
  cidr(e2) {
    return this._addCheck({ kind: "cidr", ...b.errToObj(e2) });
  }
  datetime(e2) {
    return "string" == typeof e2 ? this._addCheck({ kind: "datetime", precision: null, offset: false, local: false, message: e2 }) : this._addCheck({ kind: "datetime", precision: void 0 === (e2 == null ? void 0 : e2.precision) ? null : e2 == null ? void 0 : e2.precision, offset: (e2 == null ? void 0 : e2.offset) ?? false, local: (e2 == null ? void 0 : e2.local) ?? false, ...b.errToObj(e2 == null ? void 0 : e2.message) });
  }
  date(e2) {
    return this._addCheck({ kind: "date", message: e2 });
  }
  time(e2) {
    return "string" == typeof e2 ? this._addCheck({ kind: "time", precision: null, message: e2 }) : this._addCheck({ kind: "time", precision: void 0 === (e2 == null ? void 0 : e2.precision) ? null : e2 == null ? void 0 : e2.precision, ...b.errToObj(e2 == null ? void 0 : e2.message) });
  }
  duration(e2) {
    return this._addCheck({ kind: "duration", ...b.errToObj(e2) });
  }
  regex(e2, t2) {
    return this._addCheck({ kind: "regex", regex: e2, ...b.errToObj(t2) });
  }
  includes(e2, t2) {
    return this._addCheck({ kind: "includes", value: e2, position: t2 == null ? void 0 : t2.position, ...b.errToObj(t2 == null ? void 0 : t2.message) });
  }
  startsWith(e2, t2) {
    return this._addCheck({ kind: "startsWith", value: e2, ...b.errToObj(t2) });
  }
  endsWith(e2, t2) {
    return this._addCheck({ kind: "endsWith", value: e2, ...b.errToObj(t2) });
  }
  min(e2, t2) {
    return this._addCheck({ kind: "min", value: e2, ...b.errToObj(t2) });
  }
  max(e2, t2) {
    return this._addCheck({ kind: "max", value: e2, ...b.errToObj(t2) });
  }
  length(e2, t2) {
    return this._addCheck({ kind: "length", value: e2, ...b.errToObj(t2) });
  }
  nonempty(e2) {
    return this.min(1, b.errToObj(e2));
  }
  trim() {
    return new W({ ...this._def, checks: [...this._def.checks, { kind: "trim" }] });
  }
  toLowerCase() {
    return new W({ ...this._def, checks: [...this._def.checks, { kind: "toLowerCase" }] });
  }
  toUpperCase() {
    return new W({ ...this._def, checks: [...this._def.checks, { kind: "toUpperCase" }] });
  }
  get isDatetime() {
    return !!this._def.checks.find(((e2) => "datetime" === e2.kind));
  }
  get isDate() {
    return !!this._def.checks.find(((e2) => "date" === e2.kind));
  }
  get isTime() {
    return !!this._def.checks.find(((e2) => "time" === e2.kind));
  }
  get isDuration() {
    return !!this._def.checks.find(((e2) => "duration" === e2.kind));
  }
  get isEmail() {
    return !!this._def.checks.find(((e2) => "email" === e2.kind));
  }
  get isURL() {
    return !!this._def.checks.find(((e2) => "url" === e2.kind));
  }
  get isEmoji() {
    return !!this._def.checks.find(((e2) => "emoji" === e2.kind));
  }
  get isUUID() {
    return !!this._def.checks.find(((e2) => "uuid" === e2.kind));
  }
  get isNANOID() {
    return !!this._def.checks.find(((e2) => "nanoid" === e2.kind));
  }
  get isCUID() {
    return !!this._def.checks.find(((e2) => "cuid" === e2.kind));
  }
  get isCUID2() {
    return !!this._def.checks.find(((e2) => "cuid2" === e2.kind));
  }
  get isULID() {
    return !!this._def.checks.find(((e2) => "ulid" === e2.kind));
  }
  get isIP() {
    return !!this._def.checks.find(((e2) => "ip" === e2.kind));
  }
  get isCIDR() {
    return !!this._def.checks.find(((e2) => "cidr" === e2.kind));
  }
  get isBase64() {
    return !!this._def.checks.find(((e2) => "base64" === e2.kind));
  }
  get isBase64url() {
    return !!this._def.checks.find(((e2) => "base64url" === e2.kind));
  }
  get minLength() {
    let e2 = null;
    for (const t2 of this._def.checks) "min" === t2.kind && (null === e2 || t2.value > e2) && (e2 = t2.value);
    return e2;
  }
  get maxLength() {
    let e2 = null;
    for (const t2 of this._def.checks) "max" === t2.kind && (null === e2 || t2.value < e2) && (e2 = t2.value);
    return e2;
  }
}
function H(e2, t2) {
  const s2 = (e2.toString().split(".")[1] || "").length, a2 = (t2.toString().split(".")[1] || "").length, r2 = s2 > a2 ? s2 : a2;
  return Number.parseInt(e2.toFixed(r2).replace(".", "")) % Number.parseInt(t2.toFixed(r2).replace(".", "")) / 10 ** r2;
}
W.create = (e2) => new W({ checks: [], typeName: je.ZodString, coerce: (e2 == null ? void 0 : e2.coerce) ?? false, ...T(e2) });
class G extends A {
  constructor() {
    super(...arguments), this.min = this.gte, this.max = this.lte, this.step = this.multipleOf;
  }
  _parse(e2) {
    if (this._def.coerce && (e2.data = Number(e2.data)), this._getType(e2) !== o.number) {
      const t3 = this._getOrReturnCtx(e2);
      return p(t3, { code: c.invalid_type, expected: o.number, received: t3.parsedType }), g;
    }
    let t2;
    const a2 = new m();
    for (const r2 of this._def.checks) "int" === r2.kind ? s.isInteger(e2.data) || (t2 = this._getOrReturnCtx(e2, t2), p(t2, { code: c.invalid_type, expected: "integer", received: "float", message: r2.message }), a2.dirty()) : "min" === r2.kind ? (r2.inclusive ? e2.data < r2.value : e2.data <= r2.value) && (t2 = this._getOrReturnCtx(e2, t2), p(t2, { code: c.too_small, minimum: r2.value, type: "number", inclusive: r2.inclusive, exact: false, message: r2.message }), a2.dirty()) : "max" === r2.kind ? (r2.inclusive ? e2.data > r2.value : e2.data >= r2.value) && (t2 = this._getOrReturnCtx(e2, t2), p(t2, { code: c.too_big, maximum: r2.value, type: "number", inclusive: r2.inclusive, exact: false, message: r2.message }), a2.dirty()) : "multipleOf" === r2.kind ? 0 !== H(e2.data, r2.value) && (t2 = this._getOrReturnCtx(e2, t2), p(t2, { code: c.not_multiple_of, multipleOf: r2.value, message: r2.message }), a2.dirty()) : "finite" === r2.kind ? Number.isFinite(e2.data) || (t2 = this._getOrReturnCtx(e2, t2), p(t2, { code: c.not_finite, message: r2.message }), a2.dirty()) : s.assertNever(r2);
    return { status: a2.value, value: e2.data };
  }
  gte(e2, t2) {
    return this.setLimit("min", e2, true, b.toString(t2));
  }
  gt(e2, t2) {
    return this.setLimit("min", e2, false, b.toString(t2));
  }
  lte(e2, t2) {
    return this.setLimit("max", e2, true, b.toString(t2));
  }
  lt(e2, t2) {
    return this.setLimit("max", e2, false, b.toString(t2));
  }
  setLimit(e2, t2, s2, a2) {
    return new G({ ...this._def, checks: [...this._def.checks, { kind: e2, value: t2, inclusive: s2, message: b.toString(a2) }] });
  }
  _addCheck(e2) {
    return new G({ ...this._def, checks: [...this._def.checks, e2] });
  }
  int(e2) {
    return this._addCheck({ kind: "int", message: b.toString(e2) });
  }
  positive(e2) {
    return this._addCheck({ kind: "min", value: 0, inclusive: false, message: b.toString(e2) });
  }
  negative(e2) {
    return this._addCheck({ kind: "max", value: 0, inclusive: false, message: b.toString(e2) });
  }
  nonpositive(e2) {
    return this._addCheck({ kind: "max", value: 0, inclusive: true, message: b.toString(e2) });
  }
  nonnegative(e2) {
    return this._addCheck({ kind: "min", value: 0, inclusive: true, message: b.toString(e2) });
  }
  multipleOf(e2, t2) {
    return this._addCheck({ kind: "multipleOf", value: e2, message: b.toString(t2) });
  }
  finite(e2) {
    return this._addCheck({ kind: "finite", message: b.toString(e2) });
  }
  safe(e2) {
    return this._addCheck({ kind: "min", inclusive: true, value: Number.MIN_SAFE_INTEGER, message: b.toString(e2) })._addCheck({ kind: "max", inclusive: true, value: Number.MAX_SAFE_INTEGER, message: b.toString(e2) });
  }
  get minValue() {
    let e2 = null;
    for (const t2 of this._def.checks) "min" === t2.kind && (null === e2 || t2.value > e2) && (e2 = t2.value);
    return e2;
  }
  get maxValue() {
    let e2 = null;
    for (const t2 of this._def.checks) "max" === t2.kind && (null === e2 || t2.value < e2) && (e2 = t2.value);
    return e2;
  }
  get isInt() {
    return !!this._def.checks.find(((e2) => "int" === e2.kind || "multipleOf" === e2.kind && s.isInteger(e2.value)));
  }
  get isFinite() {
    let e2 = null, t2 = null;
    for (const s2 of this._def.checks) {
      if ("finite" === s2.kind || "int" === s2.kind || "multipleOf" === s2.kind) return true;
      "min" === s2.kind ? (null === t2 || s2.value > t2) && (t2 = s2.value) : "max" === s2.kind && (null === e2 || s2.value < e2) && (e2 = s2.value);
    }
    return Number.isFinite(t2) && Number.isFinite(e2);
  }
}
G.create = (e2) => new G({ checks: [], typeName: je.ZodNumber, coerce: (e2 == null ? void 0 : e2.coerce) || false, ...T(e2) });
class J extends A {
  constructor() {
    super(...arguments), this.min = this.gte, this.max = this.lte;
  }
  _parse(e2) {
    if (this._def.coerce) try {
      e2.data = BigInt(e2.data);
    } catch {
      return this._getInvalidInput(e2);
    }
    if (this._getType(e2) !== o.bigint) return this._getInvalidInput(e2);
    let t2;
    const a2 = new m();
    for (const r2 of this._def.checks) "min" === r2.kind ? (r2.inclusive ? e2.data < r2.value : e2.data <= r2.value) && (t2 = this._getOrReturnCtx(e2, t2), p(t2, { code: c.too_small, type: "bigint", minimum: r2.value, inclusive: r2.inclusive, message: r2.message }), a2.dirty()) : "max" === r2.kind ? (r2.inclusive ? e2.data > r2.value : e2.data >= r2.value) && (t2 = this._getOrReturnCtx(e2, t2), p(t2, { code: c.too_big, type: "bigint", maximum: r2.value, inclusive: r2.inclusive, message: r2.message }), a2.dirty()) : "multipleOf" === r2.kind ? e2.data % r2.value !== BigInt(0) && (t2 = this._getOrReturnCtx(e2, t2), p(t2, { code: c.not_multiple_of, multipleOf: r2.value, message: r2.message }), a2.dirty()) : s.assertNever(r2);
    return { status: a2.value, value: e2.data };
  }
  _getInvalidInput(e2) {
    const t2 = this._getOrReturnCtx(e2);
    return p(t2, { code: c.invalid_type, expected: o.bigint, received: t2.parsedType }), g;
  }
  gte(e2, t2) {
    return this.setLimit("min", e2, true, b.toString(t2));
  }
  gt(e2, t2) {
    return this.setLimit("min", e2, false, b.toString(t2));
  }
  lte(e2, t2) {
    return this.setLimit("max", e2, true, b.toString(t2));
  }
  lt(e2, t2) {
    return this.setLimit("max", e2, false, b.toString(t2));
  }
  setLimit(e2, t2, s2, a2) {
    return new J({ ...this._def, checks: [...this._def.checks, { kind: e2, value: t2, inclusive: s2, message: b.toString(a2) }] });
  }
  _addCheck(e2) {
    return new J({ ...this._def, checks: [...this._def.checks, e2] });
  }
  positive(e2) {
    return this._addCheck({ kind: "min", value: BigInt(0), inclusive: false, message: b.toString(e2) });
  }
  negative(e2) {
    return this._addCheck({ kind: "max", value: BigInt(0), inclusive: false, message: b.toString(e2) });
  }
  nonpositive(e2) {
    return this._addCheck({ kind: "max", value: BigInt(0), inclusive: true, message: b.toString(e2) });
  }
  nonnegative(e2) {
    return this._addCheck({ kind: "min", value: BigInt(0), inclusive: true, message: b.toString(e2) });
  }
  multipleOf(e2, t2) {
    return this._addCheck({ kind: "multipleOf", value: e2, message: b.toString(t2) });
  }
  get minValue() {
    let e2 = null;
    for (const t2 of this._def.checks) "min" === t2.kind && (null === e2 || t2.value > e2) && (e2 = t2.value);
    return e2;
  }
  get maxValue() {
    let e2 = null;
    for (const t2 of this._def.checks) "max" === t2.kind && (null === e2 || t2.value < e2) && (e2 = t2.value);
    return e2;
  }
}
J.create = (e2) => new J({ checks: [], typeName: je.ZodBigInt, coerce: (e2 == null ? void 0 : e2.coerce) ?? false, ...T(e2) });
class Y extends A {
  _parse(e2) {
    if (this._def.coerce && (e2.data = Boolean(e2.data)), this._getType(e2) !== o.boolean) {
      const t2 = this._getOrReturnCtx(e2);
      return p(t2, { code: c.invalid_type, expected: o.boolean, received: t2.parsedType }), g;
    }
    return y(e2.data);
  }
}
Y.create = (e2) => new Y({ typeName: je.ZodBoolean, coerce: (e2 == null ? void 0 : e2.coerce) || false, ...T(e2) });
class X extends A {
  _parse(e2) {
    if (this._def.coerce && (e2.data = new Date(e2.data)), this._getType(e2) !== o.date) {
      const t3 = this._getOrReturnCtx(e2);
      return p(t3, { code: c.invalid_type, expected: o.date, received: t3.parsedType }), g;
    }
    if (Number.isNaN(e2.data.getTime())) return p(this._getOrReturnCtx(e2), { code: c.invalid_date }), g;
    const t2 = new m();
    let a2;
    for (const r2 of this._def.checks) "min" === r2.kind ? e2.data.getTime() < r2.value && (a2 = this._getOrReturnCtx(e2, a2), p(a2, { code: c.too_small, message: r2.message, inclusive: true, exact: false, minimum: r2.value, type: "date" }), t2.dirty()) : "max" === r2.kind ? e2.data.getTime() > r2.value && (a2 = this._getOrReturnCtx(e2, a2), p(a2, { code: c.too_big, message: r2.message, inclusive: true, exact: false, maximum: r2.value, type: "date" }), t2.dirty()) : s.assertNever(r2);
    return { status: t2.value, value: new Date(e2.data.getTime()) };
  }
  _addCheck(e2) {
    return new X({ ...this._def, checks: [...this._def.checks, e2] });
  }
  min(e2, t2) {
    return this._addCheck({ kind: "min", value: e2.getTime(), message: b.toString(t2) });
  }
  max(e2, t2) {
    return this._addCheck({ kind: "max", value: e2.getTime(), message: b.toString(t2) });
  }
  get minDate() {
    let e2 = null;
    for (const t2 of this._def.checks) "min" === t2.kind && (null === e2 || t2.value > e2) && (e2 = t2.value);
    return null != e2 ? new Date(e2) : null;
  }
  get maxDate() {
    let e2 = null;
    for (const t2 of this._def.checks) "max" === t2.kind && (null === e2 || t2.value < e2) && (e2 = t2.value);
    return null != e2 ? new Date(e2) : null;
  }
}
X.create = (e2) => new X({ checks: [], coerce: (e2 == null ? void 0 : e2.coerce) || false, typeName: je.ZodDate, ...T(e2) });
class ee extends A {
  _parse(e2) {
    if (this._getType(e2) !== o.symbol) {
      const t2 = this._getOrReturnCtx(e2);
      return p(t2, { code: c.invalid_type, expected: o.symbol, received: t2.parsedType }), g;
    }
    return y(e2.data);
  }
}
ee.create = (e2) => new ee({ typeName: je.ZodSymbol, ...T(e2) });
class te extends A {
  _parse(e2) {
    if (this._getType(e2) !== o.undefined) {
      const t2 = this._getOrReturnCtx(e2);
      return p(t2, { code: c.invalid_type, expected: o.undefined, received: t2.parsedType }), g;
    }
    return y(e2.data);
  }
}
te.create = (e2) => new te({ typeName: je.ZodUndefined, ...T(e2) });
class se extends A {
  _parse(e2) {
    if (this._getType(e2) !== o.null) {
      const t2 = this._getOrReturnCtx(e2);
      return p(t2, { code: c.invalid_type, expected: o.null, received: t2.parsedType }), g;
    }
    return y(e2.data);
  }
}
se.create = (e2) => new se({ typeName: je.ZodNull, ...T(e2) });
class ae extends A {
  constructor() {
    super(...arguments), this._any = true;
  }
  _parse(e2) {
    return y(e2.data);
  }
}
ae.create = (e2) => new ae({ typeName: je.ZodAny, ...T(e2) });
class re extends A {
  constructor() {
    super(...arguments), this._unknown = true;
  }
  _parse(e2) {
    return y(e2.data);
  }
}
re.create = (e2) => new re({ typeName: je.ZodUnknown, ...T(e2) });
class ne extends A {
  _parse(e2) {
    const t2 = this._getOrReturnCtx(e2);
    return p(t2, { code: c.invalid_type, expected: o.never, received: t2.parsedType }), g;
  }
}
ne.create = (e2) => new ne({ typeName: je.ZodNever, ...T(e2) });
class ie extends A {
  _parse(e2) {
    if (this._getType(e2) !== o.undefined) {
      const t2 = this._getOrReturnCtx(e2);
      return p(t2, { code: c.invalid_type, expected: o.void, received: t2.parsedType }), g;
    }
    return y(e2.data);
  }
}
ie.create = (e2) => new ie({ typeName: je.ZodVoid, ...T(e2) });
class oe extends A {
  _parse(e2) {
    const { ctx: t2, status: s2 } = this._processInputParams(e2), a2 = this._def;
    if (t2.parsedType !== o.array) return p(t2, { code: c.invalid_type, expected: o.array, received: t2.parsedType }), g;
    if (null !== a2.exactLength) {
      const e3 = t2.data.length > a2.exactLength.value, r3 = t2.data.length < a2.exactLength.value;
      (e3 || r3) && (p(t2, { code: e3 ? c.too_big : c.too_small, minimum: r3 ? a2.exactLength.value : void 0, maximum: e3 ? a2.exactLength.value : void 0, type: "array", inclusive: true, exact: true, message: a2.exactLength.message }), s2.dirty());
    }
    if (null !== a2.minLength && t2.data.length < a2.minLength.value && (p(t2, { code: c.too_small, minimum: a2.minLength.value, type: "array", inclusive: true, exact: false, message: a2.minLength.message }), s2.dirty()), null !== a2.maxLength && t2.data.length > a2.maxLength.value && (p(t2, { code: c.too_big, maximum: a2.maxLength.value, type: "array", inclusive: true, exact: false, message: a2.maxLength.message }), s2.dirty()), t2.common.async) return Promise.all([...t2.data].map(((e3, s3) => a2.type._parseAsync(new w(t2, e3, t2.path, s3))))).then(((e3) => m.mergeArray(s2, e3)));
    const r2 = [...t2.data].map(((e3, s3) => a2.type._parseSync(new w(t2, e3, t2.path, s3))));
    return m.mergeArray(s2, r2);
  }
  get element() {
    return this._def.type;
  }
  min(e2, t2) {
    return new oe({ ...this._def, minLength: { value: e2, message: b.toString(t2) } });
  }
  max(e2, t2) {
    return new oe({ ...this._def, maxLength: { value: e2, message: b.toString(t2) } });
  }
  length(e2, t2) {
    return new oe({ ...this._def, exactLength: { value: e2, message: b.toString(t2) } });
  }
  nonempty(e2) {
    return this.min(1, e2);
  }
}
function de(e2) {
  if (e2 instanceof ce) {
    const t2 = {};
    for (const s2 in e2.shape) {
      const a2 = e2.shape[s2];
      t2[s2] = Ae.create(de(a2));
    }
    return new ce({ ...e2._def, shape: () => t2 });
  }
  return e2 instanceof oe ? new oe({ ...e2._def, type: de(e2.element) }) : e2 instanceof Ae ? Ae.create(de(e2.unwrap())) : e2 instanceof Ce ? Ce.create(de(e2.unwrap())) : e2 instanceof ge ? ge.create(e2.items.map(((e3) => de(e3)))) : e2;
}
oe.create = (e2, t2) => new oe({ type: e2, minLength: null, maxLength: null, exactLength: null, typeName: je.ZodArray, ...T(t2) });
class ce extends A {
  constructor() {
    super(...arguments), this._cached = null, this.nonstrict = this.passthrough, this.augment = this.extend;
  }
  _getCached() {
    if (null !== this._cached) return this._cached;
    const e2 = this._def.shape(), t2 = s.objectKeys(e2);
    return this._cached = { shape: e2, keys: t2 }, this._cached;
  }
  _parse(e2) {
    if (this._getType(e2) !== o.object) {
      const t3 = this._getOrReturnCtx(e2);
      return p(t3, { code: c.invalid_type, expected: o.object, received: t3.parsedType }), g;
    }
    const { status: t2, ctx: s2 } = this._processInputParams(e2), { shape: a2, keys: r2 } = this._getCached(), n2 = [];
    if (!(this._def.catchall instanceof ne && "strip" === this._def.unknownKeys)) for (const e3 in s2.data) r2.includes(e3) || n2.push(e3);
    const i2 = [];
    for (const e3 of r2) {
      const t3 = a2[e3], r3 = s2.data[e3];
      i2.push({ key: { status: "valid", value: e3 }, value: t3._parse(new w(s2, r3, s2.path, e3)), alwaysSet: e3 in s2.data });
    }
    if (this._def.catchall instanceof ne) {
      const e3 = this._def.unknownKeys;
      if ("passthrough" === e3) for (const e4 of n2) i2.push({ key: { status: "valid", value: e4 }, value: { status: "valid", value: s2.data[e4] } });
      else if ("strict" === e3) n2.length > 0 && (p(s2, { code: c.unrecognized_keys, keys: n2 }), t2.dirty());
      else if ("strip" !== e3) throw new Error("Internal ZodObject error: invalid unknownKeys value.");
    } else {
      const e3 = this._def.catchall;
      for (const t3 of n2) {
        const a3 = s2.data[t3];
        i2.push({ key: { status: "valid", value: t3 }, value: e3._parse(new w(s2, a3, s2.path, t3)), alwaysSet: t3 in s2.data });
      }
    }
    return s2.common.async ? Promise.resolve().then((async () => {
      const e3 = [];
      for (const t3 of i2) {
        const s3 = await t3.key, a3 = await t3.value;
        e3.push({ key: s3, value: a3, alwaysSet: t3.alwaysSet });
      }
      return e3;
    })).then(((e3) => m.mergeObjectSync(t2, e3))) : m.mergeObjectSync(t2, i2);
  }
  get shape() {
    return this._def.shape();
  }
  strict(e2) {
    return b.errToObj, new ce({ ...this._def, unknownKeys: "strict", ...void 0 !== e2 ? { errorMap: (t2, s2) => {
      var _a, _b;
      const a2 = ((_b = (_a = this._def).errorMap) == null ? void 0 : _b.call(_a, t2, s2).message) ?? s2.defaultError;
      return "unrecognized_keys" === t2.code ? { message: b.errToObj(e2).message ?? a2 } : { message: a2 };
    } } : {} });
  }
  strip() {
    return new ce({ ...this._def, unknownKeys: "strip" });
  }
  passthrough() {
    return new ce({ ...this._def, unknownKeys: "passthrough" });
  }
  extend(e2) {
    return new ce({ ...this._def, shape: () => ({ ...this._def.shape(), ...e2 }) });
  }
  merge(e2) {
    return new ce({ unknownKeys: e2._def.unknownKeys, catchall: e2._def.catchall, shape: () => ({ ...this._def.shape(), ...e2._def.shape() }), typeName: je.ZodObject });
  }
  setKey(e2, t2) {
    return this.augment({ [e2]: t2 });
  }
  catchall(e2) {
    return new ce({ ...this._def, catchall: e2 });
  }
  pick(e2) {
    const t2 = {};
    for (const a2 of s.objectKeys(e2)) e2[a2] && this.shape[a2] && (t2[a2] = this.shape[a2]);
    return new ce({ ...this._def, shape: () => t2 });
  }
  omit(e2) {
    const t2 = {};
    for (const a2 of s.objectKeys(this.shape)) e2[a2] || (t2[a2] = this.shape[a2]);
    return new ce({ ...this._def, shape: () => t2 });
  }
  deepPartial() {
    return de(this);
  }
  partial(e2) {
    const t2 = {};
    for (const a2 of s.objectKeys(this.shape)) {
      const s2 = this.shape[a2];
      e2 && !e2[a2] ? t2[a2] = s2 : t2[a2] = s2.optional();
    }
    return new ce({ ...this._def, shape: () => t2 });
  }
  required(e2) {
    const t2 = {};
    for (const a2 of s.objectKeys(this.shape)) if (e2 && !e2[a2]) t2[a2] = this.shape[a2];
    else {
      let e3 = this.shape[a2];
      for (; e3 instanceof Ae; ) e3 = e3._def.innerType;
      t2[a2] = e3;
    }
    return new ce({ ...this._def, shape: () => t2 });
  }
  keyof() {
    return ke(s.objectKeys(this.shape));
  }
}
ce.create = (e2, t2) => new ce({ shape: () => e2, unknownKeys: "strip", catchall: ne.create(), typeName: je.ZodObject, ...T(t2) }), ce.strictCreate = (e2, t2) => new ce({ shape: () => e2, unknownKeys: "strict", catchall: ne.create(), typeName: je.ZodObject, ...T(t2) }), ce.lazycreate = (e2, t2) => new ce({ shape: e2, unknownKeys: "strip", catchall: ne.create(), typeName: je.ZodObject, ...T(t2) });
class ue extends A {
  _parse(e2) {
    const { ctx: t2 } = this._processInputParams(e2), s2 = this._def.options;
    if (t2.common.async) return Promise.all(s2.map((async (e3) => {
      const s3 = { ...t2, common: { ...t2.common, issues: [] }, parent: null };
      return { result: await e3._parseAsync({ data: t2.data, path: t2.path, parent: s3 }), ctx: s3 };
    }))).then((function(e3) {
      for (const t3 of e3) if ("valid" === t3.result.status) return t3.result;
      for (const s4 of e3) if ("dirty" === s4.result.status) return t2.common.issues.push(...s4.ctx.common.issues), s4.result;
      const s3 = e3.map(((e4) => new u(e4.ctx.common.issues)));
      return p(t2, { code: c.invalid_union, unionErrors: s3 }), g;
    }));
    {
      let e3;
      const a2 = [];
      for (const r3 of s2) {
        const s3 = { ...t2, common: { ...t2.common, issues: [] }, parent: null }, n2 = r3._parseSync({ data: t2.data, path: t2.path, parent: s3 });
        if ("valid" === n2.status) return n2;
        "dirty" !== n2.status || e3 || (e3 = { result: n2, ctx: s3 }), s3.common.issues.length && a2.push(s3.common.issues);
      }
      if (e3) return t2.common.issues.push(...e3.ctx.common.issues), e3.result;
      const r2 = a2.map(((e4) => new u(e4)));
      return p(t2, { code: c.invalid_union, unionErrors: r2 }), g;
    }
  }
  get options() {
    return this._def.options;
  }
}
ue.create = (e2, t2) => new ue({ options: e2, typeName: je.ZodUnion, ...T(t2) });
const le = (e2) => e2 instanceof ve ? le(e2.schema) : e2 instanceof Te ? le(e2.innerType()) : e2 instanceof xe ? [e2.value] : e2 instanceof be ? e2.options : e2 instanceof we ? s.objectValues(e2.enum) : e2 instanceof Re ? le(e2._def.innerType) : e2 instanceof te ? [void 0] : e2 instanceof se ? [null] : e2 instanceof Ae ? [void 0, ...le(e2.unwrap())] : e2 instanceof Ce ? [null, ...le(e2.unwrap())] : e2 instanceof Ie || e2 instanceof Ne ? le(e2.unwrap()) : e2 instanceof Ze ? le(e2._def.innerType) : [];
class he extends A {
  _parse(e2) {
    const { ctx: t2 } = this._processInputParams(e2);
    if (t2.parsedType !== o.object) return p(t2, { code: c.invalid_type, expected: o.object, received: t2.parsedType }), g;
    const s2 = this.discriminator, a2 = t2.data[s2], r2 = this.optionsMap.get(a2);
    return r2 ? t2.common.async ? r2._parseAsync({ data: t2.data, path: t2.path, parent: t2 }) : r2._parseSync({ data: t2.data, path: t2.path, parent: t2 }) : (p(t2, { code: c.invalid_union_discriminator, options: Array.from(this.optionsMap.keys()), path: [s2] }), g);
  }
  get discriminator() {
    return this._def.discriminator;
  }
  get options() {
    return this._def.options;
  }
  get optionsMap() {
    return this._def.optionsMap;
  }
  static create(e2, t2, s2) {
    const a2 = /* @__PURE__ */ new Map();
    for (const s3 of t2) {
      const t3 = le(s3.shape[e2]);
      if (!t3.length) throw new Error(`A discriminator value for key \`${e2}\` could not be extracted from all schema options`);
      for (const r2 of t3) {
        if (a2.has(r2)) throw new Error(`Discriminator property ${String(e2)} has duplicate value ${String(r2)}`);
        a2.set(r2, s3);
      }
    }
    return new he({ typeName: je.ZodDiscriminatedUnion, discriminator: e2, options: t2, optionsMap: a2, ...T(s2) });
  }
}
function pe(e2, t2) {
  const a2 = d(e2), r2 = d(t2);
  if (e2 === t2) return { valid: true, data: e2 };
  if (a2 === o.object && r2 === o.object) {
    const a3 = s.objectKeys(t2), r3 = s.objectKeys(e2).filter(((e3) => -1 !== a3.indexOf(e3))), n2 = { ...e2, ...t2 };
    for (const s2 of r3) {
      const a4 = pe(e2[s2], t2[s2]);
      if (!a4.valid) return { valid: false };
      n2[s2] = a4.data;
    }
    return { valid: true, data: n2 };
  }
  if (a2 === o.array && r2 === o.array) {
    if (e2.length !== t2.length) return { valid: false };
    const s2 = [];
    for (let a3 = 0; a3 < e2.length; a3++) {
      const r3 = pe(e2[a3], t2[a3]);
      if (!r3.valid) return { valid: false };
      s2.push(r3.data);
    }
    return { valid: true, data: s2 };
  }
  return a2 === o.date && r2 === o.date && +e2 === +t2 ? { valid: true, data: e2 } : { valid: false };
}
class me extends A {
  _parse(e2) {
    const { status: t2, ctx: s2 } = this._processInputParams(e2), a2 = (e3, a3) => {
      if (_(e3) || _(a3)) return g;
      const r2 = pe(e3.value, a3.value);
      return r2.valid ? ((v(e3) || v(a3)) && t2.dirty(), { status: t2.value, value: r2.data }) : (p(s2, { code: c.invalid_intersection_types }), g);
    };
    return s2.common.async ? Promise.all([this._def.left._parseAsync({ data: s2.data, path: s2.path, parent: s2 }), this._def.right._parseAsync({ data: s2.data, path: s2.path, parent: s2 })]).then((([e3, t3]) => a2(e3, t3))) : a2(this._def.left._parseSync({ data: s2.data, path: s2.path, parent: s2 }), this._def.right._parseSync({ data: s2.data, path: s2.path, parent: s2 }));
  }
}
me.create = (e2, t2, s2) => new me({ left: e2, right: t2, typeName: je.ZodIntersection, ...T(s2) });
class ge extends A {
  _parse(e2) {
    const { status: t2, ctx: s2 } = this._processInputParams(e2);
    if (s2.parsedType !== o.array) return p(s2, { code: c.invalid_type, expected: o.array, received: s2.parsedType }), g;
    if (s2.data.length < this._def.items.length) return p(s2, { code: c.too_small, minimum: this._def.items.length, inclusive: true, exact: false, type: "array" }), g;
    !this._def.rest && s2.data.length > this._def.items.length && (p(s2, { code: c.too_big, maximum: this._def.items.length, inclusive: true, exact: false, type: "array" }), t2.dirty());
    const a2 = [...s2.data].map(((e3, t3) => {
      const a3 = this._def.items[t3] || this._def.rest;
      return a3 ? a3._parse(new w(s2, e3, s2.path, t3)) : null;
    })).filter(((e3) => !!e3));
    return s2.common.async ? Promise.all(a2).then(((e3) => m.mergeArray(t2, e3))) : m.mergeArray(t2, a2);
  }
  get items() {
    return this._def.items;
  }
  rest(e2) {
    return new ge({ ...this._def, rest: e2 });
  }
}
ge.create = (e2, t2) => {
  if (!Array.isArray(e2)) throw new Error("You must pass an array of schemas to z.tuple([ ... ])");
  return new ge({ items: e2, typeName: je.ZodTuple, rest: null, ...T(t2) });
};
class fe extends A {
  get keySchema() {
    return this._def.keyType;
  }
  get valueSchema() {
    return this._def.valueType;
  }
  _parse(e2) {
    const { status: t2, ctx: s2 } = this._processInputParams(e2);
    if (s2.parsedType !== o.object) return p(s2, { code: c.invalid_type, expected: o.object, received: s2.parsedType }), g;
    const a2 = [], r2 = this._def.keyType, n2 = this._def.valueType;
    for (const e3 in s2.data) a2.push({ key: r2._parse(new w(s2, e3, s2.path, e3)), value: n2._parse(new w(s2, s2.data[e3], s2.path, e3)), alwaysSet: e3 in s2.data });
    return s2.common.async ? m.mergeObjectAsync(t2, a2) : m.mergeObjectSync(t2, a2);
  }
  get element() {
    return this._def.valueType;
  }
  static create(e2, t2, s2) {
    return new fe(t2 instanceof A ? { keyType: e2, valueType: t2, typeName: je.ZodRecord, ...T(s2) } : { keyType: W.create(), valueType: e2, typeName: je.ZodRecord, ...T(t2) });
  }
}
class ye extends A {
  get keySchema() {
    return this._def.keyType;
  }
  get valueSchema() {
    return this._def.valueType;
  }
  _parse(e2) {
    const { status: t2, ctx: s2 } = this._processInputParams(e2);
    if (s2.parsedType !== o.map) return p(s2, { code: c.invalid_type, expected: o.map, received: s2.parsedType }), g;
    const a2 = this._def.keyType, r2 = this._def.valueType, n2 = [...s2.data.entries()].map((([e3, t3], n3) => ({ key: a2._parse(new w(s2, e3, s2.path, [n3, "key"])), value: r2._parse(new w(s2, t3, s2.path, [n3, "value"])) })));
    if (s2.common.async) {
      const e3 = /* @__PURE__ */ new Map();
      return Promise.resolve().then((async () => {
        for (const s3 of n2) {
          const a3 = await s3.key, r3 = await s3.value;
          if ("aborted" === a3.status || "aborted" === r3.status) return g;
          "dirty" !== a3.status && "dirty" !== r3.status || t2.dirty(), e3.set(a3.value, r3.value);
        }
        return { status: t2.value, value: e3 };
      }));
    }
    {
      const e3 = /* @__PURE__ */ new Map();
      for (const s3 of n2) {
        const a3 = s3.key, r3 = s3.value;
        if ("aborted" === a3.status || "aborted" === r3.status) return g;
        "dirty" !== a3.status && "dirty" !== r3.status || t2.dirty(), e3.set(a3.value, r3.value);
      }
      return { status: t2.value, value: e3 };
    }
  }
}
ye.create = (e2, t2, s2) => new ye({ valueType: t2, keyType: e2, typeName: je.ZodMap, ...T(s2) });
class _e extends A {
  _parse(e2) {
    const { status: t2, ctx: s2 } = this._processInputParams(e2);
    if (s2.parsedType !== o.set) return p(s2, { code: c.invalid_type, expected: o.set, received: s2.parsedType }), g;
    const a2 = this._def;
    null !== a2.minSize && s2.data.size < a2.minSize.value && (p(s2, { code: c.too_small, minimum: a2.minSize.value, type: "set", inclusive: true, exact: false, message: a2.minSize.message }), t2.dirty()), null !== a2.maxSize && s2.data.size > a2.maxSize.value && (p(s2, { code: c.too_big, maximum: a2.maxSize.value, type: "set", inclusive: true, exact: false, message: a2.maxSize.message }), t2.dirty());
    const r2 = this._def.valueType;
    function n2(e3) {
      const s3 = /* @__PURE__ */ new Set();
      for (const a3 of e3) {
        if ("aborted" === a3.status) return g;
        "dirty" === a3.status && t2.dirty(), s3.add(a3.value);
      }
      return { status: t2.value, value: s3 };
    }
    const i2 = [...s2.data.values()].map(((e3, t3) => r2._parse(new w(s2, e3, s2.path, t3))));
    return s2.common.async ? Promise.all(i2).then(((e3) => n2(e3))) : n2(i2);
  }
  min(e2, t2) {
    return new _e({ ...this._def, minSize: { value: e2, message: b.toString(t2) } });
  }
  max(e2, t2) {
    return new _e({ ...this._def, maxSize: { value: e2, message: b.toString(t2) } });
  }
  size(e2, t2) {
    return this.min(e2, t2).max(e2, t2);
  }
  nonempty(e2) {
    return this.min(1, e2);
  }
}
_e.create = (e2, t2) => new _e({ valueType: e2, minSize: null, maxSize: null, typeName: je.ZodSet, ...T(t2) });
class ve extends A {
  get schema() {
    return this._def.getter();
  }
  _parse(e2) {
    const { ctx: t2 } = this._processInputParams(e2);
    return this._def.getter()._parse({ data: t2.data, path: t2.path, parent: t2 });
  }
}
ve.create = (e2, t2) => new ve({ getter: e2, typeName: je.ZodLazy, ...T(t2) });
class xe extends A {
  _parse(e2) {
    if (e2.data !== this._def.value) {
      const t2 = this._getOrReturnCtx(e2);
      return p(t2, { received: t2.data, code: c.invalid_literal, expected: this._def.value }), g;
    }
    return { status: "valid", value: e2.data };
  }
  get value() {
    return this._def.value;
  }
}
function ke(e2, t2) {
  return new be({ values: e2, typeName: je.ZodEnum, ...T(t2) });
}
xe.create = (e2, t2) => new xe({ value: e2, typeName: je.ZodLiteral, ...T(t2) });
class be extends A {
  _parse(e2) {
    if ("string" != typeof e2.data) {
      const t2 = this._getOrReturnCtx(e2), a2 = this._def.values;
      return p(t2, { expected: s.joinValues(a2), received: t2.parsedType, code: c.invalid_type }), g;
    }
    if (this._cache || (this._cache = new Set(this._def.values)), !this._cache.has(e2.data)) {
      const t2 = this._getOrReturnCtx(e2), s2 = this._def.values;
      return p(t2, { received: t2.data, code: c.invalid_enum_value, options: s2 }), g;
    }
    return y(e2.data);
  }
  get options() {
    return this._def.values;
  }
  get enum() {
    const e2 = {};
    for (const t2 of this._def.values) e2[t2] = t2;
    return e2;
  }
  get Values() {
    const e2 = {};
    for (const t2 of this._def.values) e2[t2] = t2;
    return e2;
  }
  get Enum() {
    const e2 = {};
    for (const t2 of this._def.values) e2[t2] = t2;
    return e2;
  }
  extract(e2, t2 = this._def) {
    return be.create(e2, { ...this._def, ...t2 });
  }
  exclude(e2, t2 = this._def) {
    return be.create(this.options.filter(((t3) => !e2.includes(t3))), { ...this._def, ...t2 });
  }
}
be.create = ke;
class we extends A {
  _parse(e2) {
    const t2 = s.getValidEnumValues(this._def.values), a2 = this._getOrReturnCtx(e2);
    if (a2.parsedType !== o.string && a2.parsedType !== o.number) {
      const e3 = s.objectValues(t2);
      return p(a2, { expected: s.joinValues(e3), received: a2.parsedType, code: c.invalid_type }), g;
    }
    if (this._cache || (this._cache = new Set(s.getValidEnumValues(this._def.values))), !this._cache.has(e2.data)) {
      const e3 = s.objectValues(t2);
      return p(a2, { received: a2.data, code: c.invalid_enum_value, options: e3 }), g;
    }
    return y(e2.data);
  }
  get enum() {
    return this._def.values;
  }
}
we.create = (e2, t2) => new we({ values: e2, typeName: je.ZodNativeEnum, ...T(t2) });
class Se extends A {
  unwrap() {
    return this._def.type;
  }
  _parse(e2) {
    const { ctx: t2 } = this._processInputParams(e2);
    if (t2.parsedType !== o.promise && false === t2.common.async) return p(t2, { code: c.invalid_type, expected: o.promise, received: t2.parsedType }), g;
    const s2 = t2.parsedType === o.promise ? t2.data : Promise.resolve(t2.data);
    return y(s2.then(((e3) => this._def.type.parseAsync(e3, { path: t2.path, errorMap: t2.common.contextualErrorMap }))));
  }
}
Se.create = (e2, t2) => new Se({ type: e2, typeName: je.ZodPromise, ...T(t2) });
class Te extends A {
  innerType() {
    return this._def.schema;
  }
  sourceType() {
    return this._def.schema._def.typeName === je.ZodEffects ? this._def.schema.sourceType() : this._def.schema;
  }
  _parse(e2) {
    const { status: t2, ctx: a2 } = this._processInputParams(e2), r2 = this._def.effect || null, n2 = { addIssue: (e3) => {
      p(a2, e3), e3.fatal ? t2.abort() : t2.dirty();
    }, get path() {
      return a2.path;
    } };
    if (n2.addIssue = n2.addIssue.bind(n2), "preprocess" === r2.type) {
      const e3 = r2.transform(a2.data, n2);
      if (a2.common.async) return Promise.resolve(e3).then((async (e4) => {
        if ("aborted" === t2.value) return g;
        const s2 = await this._def.schema._parseAsync({ data: e4, path: a2.path, parent: a2 });
        return "aborted" === s2.status ? g : "dirty" === s2.status || "dirty" === t2.value ? f(s2.value) : s2;
      }));
      {
        if ("aborted" === t2.value) return g;
        const s2 = this._def.schema._parseSync({ data: e3, path: a2.path, parent: a2 });
        return "aborted" === s2.status ? g : "dirty" === s2.status || "dirty" === t2.value ? f(s2.value) : s2;
      }
    }
    if ("refinement" === r2.type) {
      const e3 = (e4) => {
        const t3 = r2.refinement(e4, n2);
        if (a2.common.async) return Promise.resolve(t3);
        if (t3 instanceof Promise) throw new Error("Async refinement encountered during synchronous parse operation. Use .parseAsync instead.");
        return e4;
      };
      if (false === a2.common.async) {
        const s2 = this._def.schema._parseSync({ data: a2.data, path: a2.path, parent: a2 });
        return "aborted" === s2.status ? g : ("dirty" === s2.status && t2.dirty(), e3(s2.value), { status: t2.value, value: s2.value });
      }
      return this._def.schema._parseAsync({ data: a2.data, path: a2.path, parent: a2 }).then(((s2) => "aborted" === s2.status ? g : ("dirty" === s2.status && t2.dirty(), e3(s2.value).then((() => ({ status: t2.value, value: s2.value }))))));
    }
    if ("transform" === r2.type) {
      if (false === a2.common.async) {
        const e3 = this._def.schema._parseSync({ data: a2.data, path: a2.path, parent: a2 });
        if (!x(e3)) return g;
        const s2 = r2.transform(e3.value, n2);
        if (s2 instanceof Promise) throw new Error("Asynchronous transform encountered during synchronous parse operation. Use .parseAsync instead.");
        return { status: t2.value, value: s2 };
      }
      return this._def.schema._parseAsync({ data: a2.data, path: a2.path, parent: a2 }).then(((e3) => x(e3) ? Promise.resolve(r2.transform(e3.value, n2)).then(((e4) => ({ status: t2.value, value: e4 }))) : g));
    }
    s.assertNever(r2);
  }
}
Te.create = (e2, t2, s2) => new Te({ schema: e2, typeName: je.ZodEffects, effect: t2, ...T(s2) }), Te.createWithPreprocess = (e2, t2, s2) => new Te({ schema: t2, effect: { type: "preprocess", transform: e2 }, typeName: je.ZodEffects, ...T(s2) });
class Ae extends A {
  _parse(e2) {
    return this._getType(e2) === o.undefined ? y(void 0) : this._def.innerType._parse(e2);
  }
  unwrap() {
    return this._def.innerType;
  }
}
Ae.create = (e2, t2) => new Ae({ innerType: e2, typeName: je.ZodOptional, ...T(t2) });
class Ce extends A {
  _parse(e2) {
    return this._getType(e2) === o.null ? y(null) : this._def.innerType._parse(e2);
  }
  unwrap() {
    return this._def.innerType;
  }
}
Ce.create = (e2, t2) => new Ce({ innerType: e2, typeName: je.ZodNullable, ...T(t2) });
class Re extends A {
  _parse(e2) {
    const { ctx: t2 } = this._processInputParams(e2);
    let s2 = t2.data;
    return t2.parsedType === o.undefined && (s2 = this._def.defaultValue()), this._def.innerType._parse({ data: s2, path: t2.path, parent: t2 });
  }
  removeDefault() {
    return this._def.innerType;
  }
}
Re.create = (e2, t2) => new Re({ innerType: e2, typeName: je.ZodDefault, defaultValue: "function" == typeof t2.default ? t2.default : () => t2.default, ...T(t2) });
class Ze extends A {
  _parse(e2) {
    const { ctx: t2 } = this._processInputParams(e2), s2 = { ...t2, common: { ...t2.common, issues: [] } }, a2 = this._def.innerType._parse({ data: s2.data, path: s2.path, parent: { ...s2 } });
    return k(a2) ? a2.then(((e3) => ({ status: "valid", value: "valid" === e3.status ? e3.value : this._def.catchValue({ get error() {
      return new u(s2.common.issues);
    }, input: s2.data }) }))) : { status: "valid", value: "valid" === a2.status ? a2.value : this._def.catchValue({ get error() {
      return new u(s2.common.issues);
    }, input: s2.data }) };
  }
  removeCatch() {
    return this._def.innerType;
  }
}
Ze.create = (e2, t2) => new Ze({ innerType: e2, typeName: je.ZodCatch, catchValue: "function" == typeof t2.catch ? t2.catch : () => t2.catch, ...T(t2) });
class Oe extends A {
  _parse(e2) {
    if (this._getType(e2) !== o.nan) {
      const t2 = this._getOrReturnCtx(e2);
      return p(t2, { code: c.invalid_type, expected: o.nan, received: t2.parsedType }), g;
    }
    return { status: "valid", value: e2.data };
  }
}
Oe.create = (e2) => new Oe({ typeName: je.ZodNaN, ...T(e2) });
class Ie extends A {
  _parse(e2) {
    const { ctx: t2 } = this._processInputParams(e2), s2 = t2.data;
    return this._def.type._parse({ data: s2, path: t2.path, parent: t2 });
  }
  unwrap() {
    return this._def.type;
  }
}
class Ee extends A {
  _parse(e2) {
    const { status: t2, ctx: s2 } = this._processInputParams(e2);
    if (s2.common.async) return (async () => {
      const e3 = await this._def.in._parseAsync({ data: s2.data, path: s2.path, parent: s2 });
      return "aborted" === e3.status ? g : "dirty" === e3.status ? (t2.dirty(), f(e3.value)) : this._def.out._parseAsync({ data: e3.value, path: s2.path, parent: s2 });
    })();
    {
      const e3 = this._def.in._parseSync({ data: s2.data, path: s2.path, parent: s2 });
      return "aborted" === e3.status ? g : "dirty" === e3.status ? (t2.dirty(), { status: "dirty", value: e3.value }) : this._def.out._parseSync({ data: e3.value, path: s2.path, parent: s2 });
    }
  }
  static create(e2, t2) {
    return new Ee({ in: e2, out: t2, typeName: je.ZodPipeline });
  }
}
class Ne extends A {
  _parse(e2) {
    const t2 = this._def.innerType._parse(e2), s2 = (e3) => (x(e3) && (e3.value = Object.freeze(e3.value)), e3);
    return k(t2) ? t2.then(((e3) => s2(e3))) : s2(t2);
  }
  unwrap() {
    return this._def.innerType;
  }
}
var je;
Ne.create = (e2, t2) => new Ne({ innerType: e2, typeName: je.ZodReadonly, ...T(t2) }), (function(e2) {
  e2.ZodString = "ZodString", e2.ZodNumber = "ZodNumber", e2.ZodNaN = "ZodNaN", e2.ZodBigInt = "ZodBigInt", e2.ZodBoolean = "ZodBoolean", e2.ZodDate = "ZodDate", e2.ZodSymbol = "ZodSymbol", e2.ZodUndefined = "ZodUndefined", e2.ZodNull = "ZodNull", e2.ZodAny = "ZodAny", e2.ZodUnknown = "ZodUnknown", e2.ZodNever = "ZodNever", e2.ZodVoid = "ZodVoid", e2.ZodArray = "ZodArray", e2.ZodObject = "ZodObject", e2.ZodUnion = "ZodUnion", e2.ZodDiscriminatedUnion = "ZodDiscriminatedUnion", e2.ZodIntersection = "ZodIntersection", e2.ZodTuple = "ZodTuple", e2.ZodRecord = "ZodRecord", e2.ZodMap = "ZodMap", e2.ZodSet = "ZodSet", e2.ZodFunction = "ZodFunction", e2.ZodLazy = "ZodLazy", e2.ZodLiteral = "ZodLiteral", e2.ZodEnum = "ZodEnum", e2.ZodEffects = "ZodEffects", e2.ZodNativeEnum = "ZodNativeEnum", e2.ZodOptional = "ZodOptional", e2.ZodNullable = "ZodNullable", e2.ZodDefault = "ZodDefault", e2.ZodCatch = "ZodCatch", e2.ZodPromise = "ZodPromise", e2.ZodBranded = "ZodBranded", e2.ZodPipeline = "ZodPipeline", e2.ZodReadonly = "ZodReadonly";
})(je || (je = {}));
const Pe = W.create, $e = G.create, Me = Y.create, De = re.create, Fe = (ne.create, oe.create), qe = ce.create, ze = ue.create, Le = he.create, Ve = (me.create, ge.create, fe.create), Ue = xe.create, Ke = be.create, Qe = (Se.create, Ae.create), Be = (Ce.create, "2.0"), We = ze([Pe(), $e().int()]), He = Pe(), Ge = qe({ progressToken: Qe(We) }).passthrough(), Je = qe({ _meta: Qe(Ge) }).passthrough(), Ye = qe({ method: Pe(), params: Qe(Je) }), Xe = qe({ _meta: Qe(qe({}).passthrough()) }).passthrough(), et = qe({ method: Pe(), params: Qe(Xe) }), tt = qe({ _meta: Qe(qe({}).passthrough()) }).passthrough(), st = ze([Pe(), $e().int()]), at = qe({ jsonrpc: Ue(Be), id: st }).merge(Ye).strict(), rt = qe({ jsonrpc: Ue(Be) }).merge(et).strict(), nt = qe({ jsonrpc: Ue(Be), id: st, result: tt }).strict();
var it;
!(function(e2) {
  e2[e2.ConnectionClosed = -32e3] = "ConnectionClosed", e2[e2.RequestTimeout = -32001] = "RequestTimeout", e2[e2.ParseError = -32700] = "ParseError", e2[e2.InvalidRequest = -32600] = "InvalidRequest", e2[e2.MethodNotFound = -32601] = "MethodNotFound", e2[e2.InvalidParams = -32602] = "InvalidParams", e2[e2.InternalError = -32603] = "InternalError";
})(it || (it = {}));
const ot = ze([at, rt, nt, qe({ jsonrpc: Ue(Be), id: st, error: qe({ code: $e().int(), message: Pe(), data: Qe(De()) }) }).strict()]), dt = tt.strict(), ct = et.extend({ method: Ue("notifications/cancelled"), params: Xe.extend({ requestId: st, reason: Pe().optional() }) }), ut = qe({ name: Pe(), title: Qe(Pe()) }).passthrough(), lt = ut.extend({ version: Pe() }), ht = qe({ experimental: Qe(qe({}).passthrough()), sampling: Qe(qe({}).passthrough()), elicitation: Qe(qe({}).passthrough()), roots: Qe(qe({ listChanged: Qe(Me()) }).passthrough()) }).passthrough(), pt = Ye.extend({ method: Ue("initialize"), params: Je.extend({ protocolVersion: Pe(), capabilities: ht, clientInfo: lt }) }), mt = qe({ experimental: Qe(qe({}).passthrough()), logging: Qe(qe({}).passthrough()), completions: Qe(qe({}).passthrough()), prompts: Qe(qe({ listChanged: Qe(Me()) }).passthrough()), resources: Qe(qe({ subscribe: Qe(Me()), listChanged: Qe(Me()) }).passthrough()), tools: Qe(qe({ listChanged: Qe(Me()) }).passthrough()) }).passthrough(), gt = tt.extend({ protocolVersion: Pe(), capabilities: mt, serverInfo: lt, instructions: Qe(Pe()) }), ft = et.extend({ method: Ue("notifications/initialized") }), yt = Ye.extend({ method: Ue("ping") }), _t = qe({ progress: $e(), total: Qe($e()), message: Qe(Pe()) }).passthrough(), vt = et.extend({ method: Ue("notifications/progress"), params: Xe.merge(_t).extend({ progressToken: We }) }), xt = Ye.extend({ params: Je.extend({ cursor: Qe(He) }).optional() }), kt = tt.extend({ nextCursor: Qe(He) }), bt = qe({ uri: Pe(), mimeType: Qe(Pe()), _meta: Qe(qe({}).passthrough()) }).passthrough(), wt = bt.extend({ text: Pe() }), St = bt.extend({ blob: Pe().base64() }), Tt = ut.extend({ uri: Pe(), description: Qe(Pe()), mimeType: Qe(Pe()), _meta: Qe(qe({}).passthrough()) }), At = ut.extend({ uriTemplate: Pe(), description: Qe(Pe()), mimeType: Qe(Pe()), _meta: Qe(qe({}).passthrough()) }), Ct = xt.extend({ method: Ue("resources/list") }), Rt = kt.extend({ resources: Fe(Tt) }), Zt = xt.extend({ method: Ue("resources/templates/list") }), Ot = kt.extend({ resourceTemplates: Fe(At) }), It = Ye.extend({ method: Ue("resources/read"), params: Je.extend({ uri: Pe() }) }), Et = tt.extend({ contents: Fe(ze([wt, St])) }), Nt = et.extend({ method: Ue("notifications/resources/list_changed") }), jt = Ye.extend({ method: Ue("resources/subscribe"), params: Je.extend({ uri: Pe() }) }), Pt = Ye.extend({ method: Ue("resources/unsubscribe"), params: Je.extend({ uri: Pe() }) }), $t = et.extend({ method: Ue("notifications/resources/updated"), params: Xe.extend({ uri: Pe() }) }), Mt = qe({ name: Pe(), description: Qe(Pe()), required: Qe(Me()) }).passthrough(), Dt = ut.extend({ description: Qe(Pe()), arguments: Qe(Fe(Mt)), _meta: Qe(qe({}).passthrough()) }), Ft = xt.extend({ method: Ue("prompts/list") }), qt = kt.extend({ prompts: Fe(Dt) }), zt = Ye.extend({ method: Ue("prompts/get"), params: Je.extend({ name: Pe(), arguments: Qe(Ve(Pe())) }) }), Lt = qe({ type: Ue("text"), text: Pe(), _meta: Qe(qe({}).passthrough()) }).passthrough(), Vt = qe({ type: Ue("image"), data: Pe().base64(), mimeType: Pe(), _meta: Qe(qe({}).passthrough()) }).passthrough(), Ut = qe({ type: Ue("audio"), data: Pe().base64(), mimeType: Pe(), _meta: Qe(qe({}).passthrough()) }).passthrough(), Kt = qe({ type: Ue("resource"), resource: ze([wt, St]), _meta: Qe(qe({}).passthrough()) }).passthrough(), Qt = ze([Lt, Vt, Ut, Tt.extend({ type: Ue("resource_link") }), Kt]), Bt = qe({ role: Ke(["user", "assistant"]), content: Qt }).passthrough(), Wt = tt.extend({ description: Qe(Pe()), messages: Fe(Bt) }), Ht = et.extend({ method: Ue("notifications/prompts/list_changed") }), Gt = qe({ title: Qe(Pe()), readOnlyHint: Qe(Me()), destructiveHint: Qe(Me()), idempotentHint: Qe(Me()), openWorldHint: Qe(Me()) }).passthrough(), Jt = ut.extend({ description: Qe(Pe()), inputSchema: qe({ type: Ue("object"), properties: Qe(qe({}).passthrough()), required: Qe(Fe(Pe())) }).passthrough(), outputSchema: Qe(qe({ type: Ue("object"), properties: Qe(qe({}).passthrough()), required: Qe(Fe(Pe())) }).passthrough()), annotations: Qe(Gt), _meta: Qe(qe({}).passthrough()) }), Yt = xt.extend({ method: Ue("tools/list") }), Xt = kt.extend({ tools: Fe(Jt) }), es = tt.extend({ content: Fe(Qt).default([]), structuredContent: qe({}).passthrough().optional(), isError: Qe(Me()) }), ts = (es.or(tt.extend({ toolResult: De() })), Ye.extend({ method: Ue("tools/call"), params: Je.extend({ name: Pe(), arguments: Qe(Ve(De())) }) })), ss = et.extend({ method: Ue("notifications/tools/list_changed") }), as = Ke(["debug", "info", "notice", "warning", "error", "critical", "alert", "emergency"]), rs = Ye.extend({ method: Ue("logging/setLevel"), params: Je.extend({ level: as }) }), ns = et.extend({ method: Ue("notifications/message"), params: Xe.extend({ level: as, logger: Qe(Pe()), data: De() }) }), is = qe({ name: Pe().optional() }).passthrough(), os = qe({ hints: Qe(Fe(is)), costPriority: Qe($e().min(0).max(1)), speedPriority: Qe($e().min(0).max(1)), intelligencePriority: Qe($e().min(0).max(1)) }).passthrough(), ds = qe({ role: Ke(["user", "assistant"]), content: ze([Lt, Vt, Ut]) }).passthrough(), cs = Ye.extend({ method: Ue("sampling/createMessage"), params: Je.extend({ messages: Fe(ds), systemPrompt: Qe(Pe()), includeContext: Qe(Ke(["none", "thisServer", "allServers"])), temperature: Qe($e()), maxTokens: $e().int(), stopSequences: Qe(Fe(Pe())), metadata: Qe(qe({}).passthrough()), modelPreferences: Qe(os) }) }), us = tt.extend({ model: Pe(), stopReason: Qe(Ke(["endTurn", "stopSequence", "maxTokens"]).or(Pe())), role: Ke(["user", "assistant"]), content: Le("type", [Lt, Vt, Ut]) }), ls = ze([qe({ type: Ue("boolean"), title: Qe(Pe()), description: Qe(Pe()), default: Qe(Me()) }).passthrough(), qe({ type: Ue("string"), title: Qe(Pe()), description: Qe(Pe()), minLength: Qe($e()), maxLength: Qe($e()), format: Qe(Ke(["email", "uri", "date", "date-time"])) }).passthrough(), qe({ type: Ke(["number", "integer"]), title: Qe(Pe()), description: Qe(Pe()), minimum: Qe($e()), maximum: Qe($e()) }).passthrough(), qe({ type: Ue("string"), title: Qe(Pe()), description: Qe(Pe()), enum: Fe(Pe()), enumNames: Qe(Fe(Pe())) }).passthrough()]), hs = Ye.extend({ method: Ue("elicitation/create"), params: Je.extend({ message: Pe(), requestedSchema: qe({ type: Ue("object"), properties: Ve(Pe(), ls), required: Qe(Fe(Pe())) }).passthrough() }) }), ps = tt.extend({ action: Ke(["accept", "reject", "cancel"]), content: Qe(Ve(Pe(), De())) }), ms = qe({ type: Ue("ref/resource"), uri: Pe() }).passthrough(), gs = qe({ type: Ue("ref/prompt"), name: Pe() }).passthrough(), fs = Ye.extend({ method: Ue("completion/complete"), params: Je.extend({ ref: ze([gs, ms]), argument: qe({ name: Pe(), value: Pe() }).passthrough(), context: Qe(qe({ arguments: Qe(Ve(Pe(), Pe())) })) }) }), ys = tt.extend({ completion: qe({ values: Fe(Pe()).max(100), total: Qe($e().int()), hasMore: Qe(Me()) }).passthrough() }), _s = qe({ uri: Pe().startsWith("file://"), name: Qe(Pe()), _meta: Qe(qe({}).passthrough()) }).passthrough(), vs = Ye.extend({ method: Ue("roots/list") }), xs = tt.extend({ roots: Fe(_s) }), ks = et.extend({ method: Ue("notifications/roots/list_changed") });
ze([yt, pt, fs, rs, zt, Ft, Ct, Zt, It, jt, Pt, ts, Yt]), ze([ct, vt, ft, ks]), ze([dt, us, ps, xs]), ze([yt, cs, hs, vs]), ze([ct, vt, ns, $t, Nt, ss, Ht]), ze([dt, gt, ys, Wt, qt, Rt, Ot, Et, es, Xt]);
class bs {
  constructor(e2, t2) {
    __publicField(this, "sessionId");
    __publicField(this, "onmessage");
    __publicField(this, "onerror");
    __publicField(this, "onclose");
    __publicField(this, "_port");
    __publicField(this, "_started", false);
    __publicField(this, "_closed", false);
    if (!e2) throw new Error("MessagePort is required");
    this._port = e2, this.sessionId = t2 || this.generateId(), this._port.onmessage = (e3) => {
      var _a, _b;
      try {
        const t3 = ot.parse(e3.data);
        (_a = this.onmessage) == null ? void 0 : _a.call(this, t3);
      } catch (e4) {
        const t3 = new Error(`Failed to parse message: ${e4}`);
        (_b = this.onerror) == null ? void 0 : _b.call(this, t3);
      }
    }, this._port.onmessageerror = (e3) => {
      var _a;
      const t3 = new Error(`MessagePort error: ${JSON.stringify(e3)}`);
      (_a = this.onerror) == null ? void 0 : _a.call(this, t3);
    };
  }
  static generateSessionId() {
    return "undefined" != typeof crypto && "function" == typeof crypto.randomUUID ? crypto.randomUUID() : `${Date.now().toString(36)}-${Math.random().toString(36).substring(2, 10)}`;
  }
  async start() {
    if (this._started) throw new Error("BrowserContextTransport already started! If using Client or Server class, note that connect() calls start() automatically.");
    if (this._closed) throw new Error("Cannot start a closed BrowserContextTransport");
    this._started = true, this._port.start();
  }
  async send(e2) {
    if (this._closed) throw new Error("Cannot send on a closed BrowserContextTransport");
    return new Promise(((t2, s2) => {
      var _a;
      try {
        this._port.postMessage(e2), t2();
      } catch (e3) {
        const t3 = e3 instanceof Error ? e3 : new Error(String(e3));
        (_a = this.onerror) == null ? void 0 : _a.call(this, t3), s2(t3);
      }
    }));
  }
  async close() {
    var _a;
    this._closed || (this._closed = true, this._port.close(), (_a = this.onclose) == null ? void 0 : _a.call(this));
  }
  generateId() {
    return bs.generateSessionId();
  }
}
class ws {
  constructor() {
    __publicField(this, "angieDetector");
    __publicField(this, "registrationQueue");
    __publicField(this, "clientManager");
    __publicField(this, "isInitialized", false);
    this.angieDetector = new r(), this.registrationQueue = new n(), this.clientManager = new i(), this.setupAngieReadyHandler(), this.setupServerInitHandler();
  }
  setupAngieReadyHandler() {
    this.angieDetector.waitForReady().then(((e2) => {
      e2.isReady ? this.handleAngieReady() : console.warn("AngieMcpSdk: Angie not detected - servers will remain queued");
    })).catch(((e2) => {
      console.error("AngieMcpSdk: Error waiting for Angie:", e2);
    }));
  }
  async handleAngieReady() {
    console.log("AngieMcpSdk: Angie is ready, processing queued registrations");
    try {
      await this.registrationQueue.processQueue((async (e2) => {
        await this.processRegistration(e2);
      })), this.isInitialized = true, console.log("AngieMcpSdk: Initialization complete");
    } catch (e2) {
      console.error("AngieMcpSdk: Error processing registration queue:", e2);
    }
  }
  async processRegistration(e2) {
    console.log(`AngieMcpSdk: Processing registration for server "${e2.config.name}"`);
    try {
      await this.clientManager.requestClientCreation(e2), console.log(`AngieMcpSdk: Successfully registered server "${e2.config.name}"`);
    } catch (t2) {
      throw console.error(`AngieMcpSdk: Failed to register server "${e2.config.name}":`, t2), t2;
    }
  }
  async registerServer(e2) {
    if (!e2.server) throw new Error("Server instance is required");
    if (!e2.name) throw new Error("Server name is required");
    if (!e2.description) throw new Error("Server description is required");
    console.log(`AngieMcpSdk: Registering server "${e2.name}"`);
    const t2 = this.registrationQueue.add(e2);
    if (this.angieDetector.isReady()) try {
      await this.processRegistration(t2), this.registrationQueue.updateStatus(t2.id, "registered"), console.log(`AngieMcpSdk: Server "${e2.name}" registered successfully`);
    } catch (e3) {
      const s2 = e3 instanceof Error ? e3.message : String(e3);
      throw this.registrationQueue.updateStatus(t2.id, "failed", s2), e3;
    }
    else console.log(`AngieMcpSdk: Server "${e2.name}" queued until Angie is ready`);
  }
  getRegistrations() {
    return this.registrationQueue.getAll();
  }
  getPendingRegistrations() {
    return this.registrationQueue.getPending();
  }
  isAngieReady() {
    return this.angieDetector.isReady();
  }
  isReady() {
    return this.isInitialized;
  }
  async waitForReady() {
    if (!(await this.angieDetector.waitForReady()).isReady) throw new Error("Angie is not available");
    for (; !this.isInitialized; ) await new Promise(((e2) => setTimeout(e2, 100)));
  }
  destroy() {
    this.registrationQueue.clear(), console.log("AngieMcpSdk: SDK destroyed");
  }
  setupServerInitHandler() {
    window.addEventListener("message", ((e2) => {
      var _a;
      ((_a = e2.data) == null ? void 0 : _a.type) === t.SDK_REQUEST_INIT_SERVER && this.handleServerInitRequest(e2);
    }));
  }
  handleServerInitRequest(e2) {
    const { clientId: t2, serverId: s2 } = e2.data.payload || {};
    if (t2 && s2) {
      console.log(`AngieMcpSdk: Handling server init request for clientId: ${t2}, serverId: ${s2}`);
      try {
        const t3 = this.registrationQueue.getAll().find(((e3) => e3.id === s2));
        if (!t3) return void console.error(`AngieMcpSdk: No registration found for serverId: ${s2}`);
        const a2 = e2.ports[0];
        if (!a2) return void console.error("AngieMcpSdk: No port provided in server init request");
        const r2 = t3.config.server, n2 = new bs(a2);
        r2.connect(n2), console.log(`AngieMcpSdk: Server "${t3.config.name}" initialized successfully`);
      } catch (e3) {
        console.error(`AngieMcpSdk: Error initializing server for clientId ${t2}:`, e3);
      }
    } else console.error("AngieMcpSdk: Invalid server init request - missing clientId or serverId");
  }
}
var util$1;
(function(util2) {
  util2.assertEqual = (_2) => {
  };
  function assertIs(_arg) {
  }
  util2.assertIs = assertIs;
  function assertNever(_x) {
    throw new Error();
  }
  util2.assertNever = assertNever;
  util2.arrayToEnum = (items2) => {
    const obj = {};
    for (const item of items2) {
      obj[item] = item;
    }
    return obj;
  };
  util2.getValidEnumValues = (obj) => {
    const validKeys = util2.objectKeys(obj).filter((k2) => typeof obj[obj[k2]] !== "number");
    const filtered = {};
    for (const k2 of validKeys) {
      filtered[k2] = obj[k2];
    }
    return util2.objectValues(filtered);
  };
  util2.objectValues = (obj) => {
    return util2.objectKeys(obj).map(function(e2) {
      return obj[e2];
    });
  };
  util2.objectKeys = typeof Object.keys === "function" ? (obj) => Object.keys(obj) : (object) => {
    const keys = [];
    for (const key in object) {
      if (Object.prototype.hasOwnProperty.call(object, key)) {
        keys.push(key);
      }
    }
    return keys;
  };
  util2.find = (arr, checker) => {
    for (const item of arr) {
      if (checker(item))
        return item;
    }
    return void 0;
  };
  util2.isInteger = typeof Number.isInteger === "function" ? (val) => Number.isInteger(val) : (val) => typeof val === "number" && Number.isFinite(val) && Math.floor(val) === val;
  function joinValues(array, separator = " | ") {
    return array.map((val) => typeof val === "string" ? `'${val}'` : val).join(separator);
  }
  util2.joinValues = joinValues;
  util2.jsonStringifyReplacer = (_2, value) => {
    if (typeof value === "bigint") {
      return value.toString();
    }
    return value;
  };
})(util$1 || (util$1 = {}));
var objectUtil;
(function(objectUtil2) {
  objectUtil2.mergeShapes = (first, second) => {
    return {
      ...first,
      ...second
      // second overwrites first
    };
  };
})(objectUtil || (objectUtil = {}));
const ZodParsedType = util$1.arrayToEnum([
  "string",
  "nan",
  "number",
  "integer",
  "float",
  "boolean",
  "date",
  "bigint",
  "symbol",
  "function",
  "undefined",
  "null",
  "array",
  "object",
  "unknown",
  "promise",
  "void",
  "never",
  "map",
  "set"
]);
const getParsedType = (data2) => {
  const t2 = typeof data2;
  switch (t2) {
    case "undefined":
      return ZodParsedType.undefined;
    case "string":
      return ZodParsedType.string;
    case "number":
      return Number.isNaN(data2) ? ZodParsedType.nan : ZodParsedType.number;
    case "boolean":
      return ZodParsedType.boolean;
    case "function":
      return ZodParsedType.function;
    case "bigint":
      return ZodParsedType.bigint;
    case "symbol":
      return ZodParsedType.symbol;
    case "object":
      if (Array.isArray(data2)) {
        return ZodParsedType.array;
      }
      if (data2 === null) {
        return ZodParsedType.null;
      }
      if (data2.then && typeof data2.then === "function" && data2.catch && typeof data2.catch === "function") {
        return ZodParsedType.promise;
      }
      if (typeof Map !== "undefined" && data2 instanceof Map) {
        return ZodParsedType.map;
      }
      if (typeof Set !== "undefined" && data2 instanceof Set) {
        return ZodParsedType.set;
      }
      if (typeof Date !== "undefined" && data2 instanceof Date) {
        return ZodParsedType.date;
      }
      return ZodParsedType.object;
    default:
      return ZodParsedType.unknown;
  }
};
const ZodIssueCode = util$1.arrayToEnum([
  "invalid_type",
  "invalid_literal",
  "custom",
  "invalid_union",
  "invalid_union_discriminator",
  "invalid_enum_value",
  "unrecognized_keys",
  "invalid_arguments",
  "invalid_return_type",
  "invalid_date",
  "invalid_string",
  "too_small",
  "too_big",
  "invalid_intersection_types",
  "not_multiple_of",
  "not_finite"
]);
class ZodError extends Error {
  get errors() {
    return this.issues;
  }
  constructor(issues) {
    super();
    this.issues = [];
    this.addIssue = (sub) => {
      this.issues = [...this.issues, sub];
    };
    this.addIssues = (subs = []) => {
      this.issues = [...this.issues, ...subs];
    };
    const actualProto = new.target.prototype;
    if (Object.setPrototypeOf) {
      Object.setPrototypeOf(this, actualProto);
    } else {
      this.__proto__ = actualProto;
    }
    this.name = "ZodError";
    this.issues = issues;
  }
  format(_mapper) {
    const mapper = _mapper || function(issue) {
      return issue.message;
    };
    const fieldErrors = { _errors: [] };
    const processError = (error) => {
      for (const issue of error.issues) {
        if (issue.code === "invalid_union") {
          issue.unionErrors.map(processError);
        } else if (issue.code === "invalid_return_type") {
          processError(issue.returnTypeError);
        } else if (issue.code === "invalid_arguments") {
          processError(issue.argumentsError);
        } else if (issue.path.length === 0) {
          fieldErrors._errors.push(mapper(issue));
        } else {
          let curr = fieldErrors;
          let i2 = 0;
          while (i2 < issue.path.length) {
            const el = issue.path[i2];
            const terminal = i2 === issue.path.length - 1;
            if (!terminal) {
              curr[el] = curr[el] || { _errors: [] };
            } else {
              curr[el] = curr[el] || { _errors: [] };
              curr[el]._errors.push(mapper(issue));
            }
            curr = curr[el];
            i2++;
          }
        }
      }
    };
    processError(this);
    return fieldErrors;
  }
  static assert(value) {
    if (!(value instanceof ZodError)) {
      throw new Error(`Not a ZodError: ${value}`);
    }
  }
  toString() {
    return this.message;
  }
  get message() {
    return JSON.stringify(this.issues, util$1.jsonStringifyReplacer, 2);
  }
  get isEmpty() {
    return this.issues.length === 0;
  }
  flatten(mapper = (issue) => issue.message) {
    const fieldErrors = {};
    const formErrors = [];
    for (const sub of this.issues) {
      if (sub.path.length > 0) {
        const firstEl = sub.path[0];
        fieldErrors[firstEl] = fieldErrors[firstEl] || [];
        fieldErrors[firstEl].push(mapper(sub));
      } else {
        formErrors.push(mapper(sub));
      }
    }
    return { formErrors, fieldErrors };
  }
  get formErrors() {
    return this.flatten();
  }
}
ZodError.create = (issues) => {
  const error = new ZodError(issues);
  return error;
};
const errorMap = (issue, _ctx) => {
  let message;
  switch (issue.code) {
    case ZodIssueCode.invalid_type:
      if (issue.received === ZodParsedType.undefined) {
        message = "Required";
      } else {
        message = `Expected ${issue.expected}, received ${issue.received}`;
      }
      break;
    case ZodIssueCode.invalid_literal:
      message = `Invalid literal value, expected ${JSON.stringify(issue.expected, util$1.jsonStringifyReplacer)}`;
      break;
    case ZodIssueCode.unrecognized_keys:
      message = `Unrecognized key(s) in object: ${util$1.joinValues(issue.keys, ", ")}`;
      break;
    case ZodIssueCode.invalid_union:
      message = `Invalid input`;
      break;
    case ZodIssueCode.invalid_union_discriminator:
      message = `Invalid discriminator value. Expected ${util$1.joinValues(issue.options)}`;
      break;
    case ZodIssueCode.invalid_enum_value:
      message = `Invalid enum value. Expected ${util$1.joinValues(issue.options)}, received '${issue.received}'`;
      break;
    case ZodIssueCode.invalid_arguments:
      message = `Invalid function arguments`;
      break;
    case ZodIssueCode.invalid_return_type:
      message = `Invalid function return type`;
      break;
    case ZodIssueCode.invalid_date:
      message = `Invalid date`;
      break;
    case ZodIssueCode.invalid_string:
      if (typeof issue.validation === "object") {
        if ("includes" in issue.validation) {
          message = `Invalid input: must include "${issue.validation.includes}"`;
          if (typeof issue.validation.position === "number") {
            message = `${message} at one or more positions greater than or equal to ${issue.validation.position}`;
          }
        } else if ("startsWith" in issue.validation) {
          message = `Invalid input: must start with "${issue.validation.startsWith}"`;
        } else if ("endsWith" in issue.validation) {
          message = `Invalid input: must end with "${issue.validation.endsWith}"`;
        } else {
          util$1.assertNever(issue.validation);
        }
      } else if (issue.validation !== "regex") {
        message = `Invalid ${issue.validation}`;
      } else {
        message = "Invalid";
      }
      break;
    case ZodIssueCode.too_small:
      if (issue.type === "array")
        message = `Array must contain ${issue.exact ? "exactly" : issue.inclusive ? `at least` : `more than`} ${issue.minimum} element(s)`;
      else if (issue.type === "string")
        message = `String must contain ${issue.exact ? "exactly" : issue.inclusive ? `at least` : `over`} ${issue.minimum} character(s)`;
      else if (issue.type === "number")
        message = `Number must be ${issue.exact ? `exactly equal to ` : issue.inclusive ? `greater than or equal to ` : `greater than `}${issue.minimum}`;
      else if (issue.type === "bigint")
        message = `Number must be ${issue.exact ? `exactly equal to ` : issue.inclusive ? `greater than or equal to ` : `greater than `}${issue.minimum}`;
      else if (issue.type === "date")
        message = `Date must be ${issue.exact ? `exactly equal to ` : issue.inclusive ? `greater than or equal to ` : `greater than `}${new Date(Number(issue.minimum))}`;
      else
        message = "Invalid input";
      break;
    case ZodIssueCode.too_big:
      if (issue.type === "array")
        message = `Array must contain ${issue.exact ? `exactly` : issue.inclusive ? `at most` : `less than`} ${issue.maximum} element(s)`;
      else if (issue.type === "string")
        message = `String must contain ${issue.exact ? `exactly` : issue.inclusive ? `at most` : `under`} ${issue.maximum} character(s)`;
      else if (issue.type === "number")
        message = `Number must be ${issue.exact ? `exactly` : issue.inclusive ? `less than or equal to` : `less than`} ${issue.maximum}`;
      else if (issue.type === "bigint")
        message = `BigInt must be ${issue.exact ? `exactly` : issue.inclusive ? `less than or equal to` : `less than`} ${issue.maximum}`;
      else if (issue.type === "date")
        message = `Date must be ${issue.exact ? `exactly` : issue.inclusive ? `smaller than or equal to` : `smaller than`} ${new Date(Number(issue.maximum))}`;
      else
        message = "Invalid input";
      break;
    case ZodIssueCode.custom:
      message = `Invalid input`;
      break;
    case ZodIssueCode.invalid_intersection_types:
      message = `Intersection results could not be merged`;
      break;
    case ZodIssueCode.not_multiple_of:
      message = `Number must be a multiple of ${issue.multipleOf}`;
      break;
    case ZodIssueCode.not_finite:
      message = "Number must be finite";
      break;
    default:
      message = _ctx.defaultError;
      util$1.assertNever(issue);
  }
  return { message };
};
let overrideErrorMap = errorMap;
function getErrorMap() {
  return overrideErrorMap;
}
const makeIssue = (params) => {
  const { data: data2, path, errorMaps, issueData } = params;
  const fullPath = [...path, ...issueData.path || []];
  const fullIssue = {
    ...issueData,
    path: fullPath
  };
  if (issueData.message !== void 0) {
    return {
      ...issueData,
      path: fullPath,
      message: issueData.message
    };
  }
  let errorMessage = "";
  const maps = errorMaps.filter((m2) => !!m2).slice().reverse();
  for (const map of maps) {
    errorMessage = map(fullIssue, { data: data2, defaultError: errorMessage }).message;
  }
  return {
    ...issueData,
    path: fullPath,
    message: errorMessage
  };
};
function addIssueToContext(ctx, issueData) {
  const overrideMap = getErrorMap();
  const issue = makeIssue({
    issueData,
    data: ctx.data,
    path: ctx.path,
    errorMaps: [
      ctx.common.contextualErrorMap,
      // contextual error map is first priority
      ctx.schemaErrorMap,
      // then schema-bound map if available
      overrideMap,
      // then global override map
      overrideMap === errorMap ? void 0 : errorMap
      // then global default map
    ].filter((x2) => !!x2)
  });
  ctx.common.issues.push(issue);
}
class ParseStatus {
  constructor() {
    this.value = "valid";
  }
  dirty() {
    if (this.value === "valid")
      this.value = "dirty";
  }
  abort() {
    if (this.value !== "aborted")
      this.value = "aborted";
  }
  static mergeArray(status, results) {
    const arrayValue = [];
    for (const s2 of results) {
      if (s2.status === "aborted")
        return INVALID;
      if (s2.status === "dirty")
        status.dirty();
      arrayValue.push(s2.value);
    }
    return { status: status.value, value: arrayValue };
  }
  static async mergeObjectAsync(status, pairs) {
    const syncPairs = [];
    for (const pair of pairs) {
      const key = await pair.key;
      const value = await pair.value;
      syncPairs.push({
        key,
        value
      });
    }
    return ParseStatus.mergeObjectSync(status, syncPairs);
  }
  static mergeObjectSync(status, pairs) {
    const finalObject = {};
    for (const pair of pairs) {
      const { key, value } = pair;
      if (key.status === "aborted")
        return INVALID;
      if (value.status === "aborted")
        return INVALID;
      if (key.status === "dirty")
        status.dirty();
      if (value.status === "dirty")
        status.dirty();
      if (key.value !== "__proto__" && (typeof value.value !== "undefined" || pair.alwaysSet)) {
        finalObject[key.value] = value.value;
      }
    }
    return { status: status.value, value: finalObject };
  }
}
const INVALID = Object.freeze({
  status: "aborted"
});
const DIRTY = (value) => ({ status: "dirty", value });
const OK = (value) => ({ status: "valid", value });
const isAborted = (x2) => x2.status === "aborted";
const isDirty = (x2) => x2.status === "dirty";
const isValid = (x2) => x2.status === "valid";
const isAsync = (x2) => typeof Promise !== "undefined" && x2 instanceof Promise;
var errorUtil;
(function(errorUtil2) {
  errorUtil2.errToObj = (message) => typeof message === "string" ? { message } : message || {};
  errorUtil2.toString = (message) => typeof message === "string" ? message : message == null ? void 0 : message.message;
})(errorUtil || (errorUtil = {}));
class ParseInputLazyPath {
  constructor(parent, value, path, key) {
    this._cachedPath = [];
    this.parent = parent;
    this.data = value;
    this._path = path;
    this._key = key;
  }
  get path() {
    if (!this._cachedPath.length) {
      if (Array.isArray(this._key)) {
        this._cachedPath.push(...this._path, ...this._key);
      } else {
        this._cachedPath.push(...this._path, this._key);
      }
    }
    return this._cachedPath;
  }
}
const handleResult = (ctx, result) => {
  if (isValid(result)) {
    return { success: true, data: result.value };
  } else {
    if (!ctx.common.issues.length) {
      throw new Error("Validation failed but no issues detected.");
    }
    return {
      success: false,
      get error() {
        if (this._error)
          return this._error;
        const error = new ZodError(ctx.common.issues);
        this._error = error;
        return this._error;
      }
    };
  }
};
function processCreateParams$1(params) {
  if (!params)
    return {};
  const { errorMap: errorMap2, invalid_type_error, required_error, description: description2 } = params;
  if (errorMap2 && (invalid_type_error || required_error)) {
    throw new Error(`Can't use "invalid_type_error" or "required_error" in conjunction with custom error map.`);
  }
  if (errorMap2)
    return { errorMap: errorMap2, description: description2 };
  const customMap = (iss, ctx) => {
    const { message } = params;
    if (iss.code === "invalid_enum_value") {
      return { message: message ?? ctx.defaultError };
    }
    if (typeof ctx.data === "undefined") {
      return { message: message ?? required_error ?? ctx.defaultError };
    }
    if (iss.code !== "invalid_type")
      return { message: ctx.defaultError };
    return { message: message ?? invalid_type_error ?? ctx.defaultError };
  };
  return { errorMap: customMap, description: description2 };
}
class ZodType {
  get description() {
    return this._def.description;
  }
  _getType(input) {
    return getParsedType(input.data);
  }
  _getOrReturnCtx(input, ctx) {
    return ctx || {
      common: input.parent.common,
      data: input.data,
      parsedType: getParsedType(input.data),
      schemaErrorMap: this._def.errorMap,
      path: input.path,
      parent: input.parent
    };
  }
  _processInputParams(input) {
    return {
      status: new ParseStatus(),
      ctx: {
        common: input.parent.common,
        data: input.data,
        parsedType: getParsedType(input.data),
        schemaErrorMap: this._def.errorMap,
        path: input.path,
        parent: input.parent
      }
    };
  }
  _parseSync(input) {
    const result = this._parse(input);
    if (isAsync(result)) {
      throw new Error("Synchronous parse encountered promise.");
    }
    return result;
  }
  _parseAsync(input) {
    const result = this._parse(input);
    return Promise.resolve(result);
  }
  parse(data2, params) {
    const result = this.safeParse(data2, params);
    if (result.success)
      return result.data;
    throw result.error;
  }
  safeParse(data2, params) {
    const ctx = {
      common: {
        issues: [],
        async: (params == null ? void 0 : params.async) ?? false,
        contextualErrorMap: params == null ? void 0 : params.errorMap
      },
      path: (params == null ? void 0 : params.path) || [],
      schemaErrorMap: this._def.errorMap,
      parent: null,
      data: data2,
      parsedType: getParsedType(data2)
    };
    const result = this._parseSync({ data: data2, path: ctx.path, parent: ctx });
    return handleResult(ctx, result);
  }
  "~validate"(data2) {
    var _a, _b;
    const ctx = {
      common: {
        issues: [],
        async: !!this["~standard"].async
      },
      path: [],
      schemaErrorMap: this._def.errorMap,
      parent: null,
      data: data2,
      parsedType: getParsedType(data2)
    };
    if (!this["~standard"].async) {
      try {
        const result = this._parseSync({ data: data2, path: [], parent: ctx });
        return isValid(result) ? {
          value: result.value
        } : {
          issues: ctx.common.issues
        };
      } catch (err) {
        if ((_b = (_a = err == null ? void 0 : err.message) == null ? void 0 : _a.toLowerCase()) == null ? void 0 : _b.includes("encountered")) {
          this["~standard"].async = true;
        }
        ctx.common = {
          issues: [],
          async: true
        };
      }
    }
    return this._parseAsync({ data: data2, path: [], parent: ctx }).then((result) => isValid(result) ? {
      value: result.value
    } : {
      issues: ctx.common.issues
    });
  }
  async parseAsync(data2, params) {
    const result = await this.safeParseAsync(data2, params);
    if (result.success)
      return result.data;
    throw result.error;
  }
  async safeParseAsync(data2, params) {
    const ctx = {
      common: {
        issues: [],
        contextualErrorMap: params == null ? void 0 : params.errorMap,
        async: true
      },
      path: (params == null ? void 0 : params.path) || [],
      schemaErrorMap: this._def.errorMap,
      parent: null,
      data: data2,
      parsedType: getParsedType(data2)
    };
    const maybeAsyncResult = this._parse({ data: data2, path: ctx.path, parent: ctx });
    const result = await (isAsync(maybeAsyncResult) ? maybeAsyncResult : Promise.resolve(maybeAsyncResult));
    return handleResult(ctx, result);
  }
  refine(check, message) {
    const getIssueProperties = (val) => {
      if (typeof message === "string" || typeof message === "undefined") {
        return { message };
      } else if (typeof message === "function") {
        return message(val);
      } else {
        return message;
      }
    };
    return this._refinement((val, ctx) => {
      const result = check(val);
      const setError = () => ctx.addIssue({
        code: ZodIssueCode.custom,
        ...getIssueProperties(val)
      });
      if (typeof Promise !== "undefined" && result instanceof Promise) {
        return result.then((data2) => {
          if (!data2) {
            setError();
            return false;
          } else {
            return true;
          }
        });
      }
      if (!result) {
        setError();
        return false;
      } else {
        return true;
      }
    });
  }
  refinement(check, refinementData) {
    return this._refinement((val, ctx) => {
      if (!check(val)) {
        ctx.addIssue(typeof refinementData === "function" ? refinementData(val, ctx) : refinementData);
        return false;
      } else {
        return true;
      }
    });
  }
  _refinement(refinement) {
    return new ZodEffects({
      schema: this,
      typeName: ZodFirstPartyTypeKind.ZodEffects,
      effect: { type: "refinement", refinement }
    });
  }
  superRefine(refinement) {
    return this._refinement(refinement);
  }
  constructor(def) {
    this.spa = this.safeParseAsync;
    this._def = def;
    this.parse = this.parse.bind(this);
    this.safeParse = this.safeParse.bind(this);
    this.parseAsync = this.parseAsync.bind(this);
    this.safeParseAsync = this.safeParseAsync.bind(this);
    this.spa = this.spa.bind(this);
    this.refine = this.refine.bind(this);
    this.refinement = this.refinement.bind(this);
    this.superRefine = this.superRefine.bind(this);
    this.optional = this.optional.bind(this);
    this.nullable = this.nullable.bind(this);
    this.nullish = this.nullish.bind(this);
    this.array = this.array.bind(this);
    this.promise = this.promise.bind(this);
    this.or = this.or.bind(this);
    this.and = this.and.bind(this);
    this.transform = this.transform.bind(this);
    this.brand = this.brand.bind(this);
    this.default = this.default.bind(this);
    this.catch = this.catch.bind(this);
    this.describe = this.describe.bind(this);
    this.pipe = this.pipe.bind(this);
    this.readonly = this.readonly.bind(this);
    this.isNullable = this.isNullable.bind(this);
    this.isOptional = this.isOptional.bind(this);
    this["~standard"] = {
      version: 1,
      vendor: "zod",
      validate: (data2) => this["~validate"](data2)
    };
  }
  optional() {
    return ZodOptional.create(this, this._def);
  }
  nullable() {
    return ZodNullable.create(this, this._def);
  }
  nullish() {
    return this.nullable().optional();
  }
  array() {
    return ZodArray.create(this);
  }
  promise() {
    return ZodPromise.create(this, this._def);
  }
  or(option) {
    return ZodUnion.create([this, option], this._def);
  }
  and(incoming) {
    return ZodIntersection.create(this, incoming, this._def);
  }
  transform(transform) {
    return new ZodEffects({
      ...processCreateParams$1(this._def),
      schema: this,
      typeName: ZodFirstPartyTypeKind.ZodEffects,
      effect: { type: "transform", transform }
    });
  }
  default(def) {
    const defaultValueFunc = typeof def === "function" ? def : () => def;
    return new ZodDefault({
      ...processCreateParams$1(this._def),
      innerType: this,
      defaultValue: defaultValueFunc,
      typeName: ZodFirstPartyTypeKind.ZodDefault
    });
  }
  brand() {
    return new ZodBranded({
      typeName: ZodFirstPartyTypeKind.ZodBranded,
      type: this,
      ...processCreateParams$1(this._def)
    });
  }
  catch(def) {
    const catchValueFunc = typeof def === "function" ? def : () => def;
    return new ZodCatch({
      ...processCreateParams$1(this._def),
      innerType: this,
      catchValue: catchValueFunc,
      typeName: ZodFirstPartyTypeKind.ZodCatch
    });
  }
  describe(description2) {
    const This = this.constructor;
    return new This({
      ...this._def,
      description: description2
    });
  }
  pipe(target) {
    return ZodPipeline.create(this, target);
  }
  readonly() {
    return ZodReadonly.create(this);
  }
  isOptional() {
    return this.safeParse(void 0).success;
  }
  isNullable() {
    return this.safeParse(null).success;
  }
}
const cuidRegex = /^c[^\s-]{8,}$/i;
const cuid2Regex = /^[0-9a-z]+$/;
const ulidRegex = /^[0-9A-HJKMNP-TV-Z]{26}$/i;
const uuidRegex = /^[0-9a-fA-F]{8}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{12}$/i;
const nanoidRegex = /^[a-z0-9_-]{21}$/i;
const jwtRegex = /^[A-Za-z0-9-_]+\.[A-Za-z0-9-_]+\.[A-Za-z0-9-_]*$/;
const durationRegex = /^[-+]?P(?!$)(?:(?:[-+]?\d+Y)|(?:[-+]?\d+[.,]\d+Y$))?(?:(?:[-+]?\d+M)|(?:[-+]?\d+[.,]\d+M$))?(?:(?:[-+]?\d+W)|(?:[-+]?\d+[.,]\d+W$))?(?:(?:[-+]?\d+D)|(?:[-+]?\d+[.,]\d+D$))?(?:T(?=[\d+-])(?:(?:[-+]?\d+H)|(?:[-+]?\d+[.,]\d+H$))?(?:(?:[-+]?\d+M)|(?:[-+]?\d+[.,]\d+M$))?(?:[-+]?\d+(?:[.,]\d+)?S)?)??$/;
const emailRegex = /^(?!\.)(?!.*\.\.)([A-Z0-9_'+\-\.]*)[A-Z0-9_+-]@([A-Z0-9][A-Z0-9\-]*\.)+[A-Z]{2,}$/i;
const _emojiRegex = `^(\\p{Extended_Pictographic}|\\p{Emoji_Component})+$`;
let emojiRegex$1;
const ipv4Regex = /^(?:(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\.){3}(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])$/;
const ipv4CidrRegex = /^(?:(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\.){3}(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\/(3[0-2]|[12]?[0-9])$/;
const ipv6Regex = /^(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]))$/;
const ipv6CidrRegex = /^(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]))\/(12[0-8]|1[01][0-9]|[1-9]?[0-9])$/;
const base64Regex = /^([0-9a-zA-Z+/]{4})*(([0-9a-zA-Z+/]{2}==)|([0-9a-zA-Z+/]{3}=))?$/;
const base64urlRegex = /^([0-9a-zA-Z-_]{4})*(([0-9a-zA-Z-_]{2}(==)?)|([0-9a-zA-Z-_]{3}(=)?))?$/;
const dateRegexSource = `((\\d\\d[2468][048]|\\d\\d[13579][26]|\\d\\d0[48]|[02468][048]00|[13579][26]00)-02-29|\\d{4}-((0[13578]|1[02])-(0[1-9]|[12]\\d|3[01])|(0[469]|11)-(0[1-9]|[12]\\d|30)|(02)-(0[1-9]|1\\d|2[0-8])))`;
const dateRegex = new RegExp(`^${dateRegexSource}$`);
function timeRegexSource(args) {
  let secondsRegexSource = `[0-5]\\d`;
  if (args.precision) {
    secondsRegexSource = `${secondsRegexSource}\\.\\d{${args.precision}}`;
  } else if (args.precision == null) {
    secondsRegexSource = `${secondsRegexSource}(\\.\\d+)?`;
  }
  const secondsQuantifier = args.precision ? "+" : "?";
  return `([01]\\d|2[0-3]):[0-5]\\d(:${secondsRegexSource})${secondsQuantifier}`;
}
function timeRegex(args) {
  return new RegExp(`^${timeRegexSource(args)}$`);
}
function datetimeRegex(args) {
  let regex = `${dateRegexSource}T${timeRegexSource(args)}`;
  const opts = [];
  opts.push(args.local ? `Z?` : `Z`);
  if (args.offset)
    opts.push(`([+-]\\d{2}:?\\d{2})`);
  regex = `${regex}(${opts.join("|")})`;
  return new RegExp(`^${regex}$`);
}
function isValidIP(ip, version) {
  if ((version === "v4" || !version) && ipv4Regex.test(ip)) {
    return true;
  }
  if ((version === "v6" || !version) && ipv6Regex.test(ip)) {
    return true;
  }
  return false;
}
function isValidJWT(jwt, alg) {
  if (!jwtRegex.test(jwt))
    return false;
  try {
    const [header] = jwt.split(".");
    if (!header)
      return false;
    const base64 = header.replace(/-/g, "+").replace(/_/g, "/").padEnd(header.length + (4 - header.length % 4) % 4, "=");
    const decoded = JSON.parse(atob(base64));
    if (typeof decoded !== "object" || decoded === null)
      return false;
    if ("typ" in decoded && (decoded == null ? void 0 : decoded.typ) !== "JWT")
      return false;
    if (!decoded.alg)
      return false;
    if (alg && decoded.alg !== alg)
      return false;
    return true;
  } catch {
    return false;
  }
}
function isValidCidr(ip, version) {
  if ((version === "v4" || !version) && ipv4CidrRegex.test(ip)) {
    return true;
  }
  if ((version === "v6" || !version) && ipv6CidrRegex.test(ip)) {
    return true;
  }
  return false;
}
class ZodString extends ZodType {
  _parse(input) {
    if (this._def.coerce) {
      input.data = String(input.data);
    }
    const parsedType = this._getType(input);
    if (parsedType !== ZodParsedType.string) {
      const ctx2 = this._getOrReturnCtx(input);
      addIssueToContext(ctx2, {
        code: ZodIssueCode.invalid_type,
        expected: ZodParsedType.string,
        received: ctx2.parsedType
      });
      return INVALID;
    }
    const status = new ParseStatus();
    let ctx = void 0;
    for (const check of this._def.checks) {
      if (check.kind === "min") {
        if (input.data.length < check.value) {
          ctx = this._getOrReturnCtx(input, ctx);
          addIssueToContext(ctx, {
            code: ZodIssueCode.too_small,
            minimum: check.value,
            type: "string",
            inclusive: true,
            exact: false,
            message: check.message
          });
          status.dirty();
        }
      } else if (check.kind === "max") {
        if (input.data.length > check.value) {
          ctx = this._getOrReturnCtx(input, ctx);
          addIssueToContext(ctx, {
            code: ZodIssueCode.too_big,
            maximum: check.value,
            type: "string",
            inclusive: true,
            exact: false,
            message: check.message
          });
          status.dirty();
        }
      } else if (check.kind === "length") {
        const tooBig = input.data.length > check.value;
        const tooSmall = input.data.length < check.value;
        if (tooBig || tooSmall) {
          ctx = this._getOrReturnCtx(input, ctx);
          if (tooBig) {
            addIssueToContext(ctx, {
              code: ZodIssueCode.too_big,
              maximum: check.value,
              type: "string",
              inclusive: true,
              exact: true,
              message: check.message
            });
          } else if (tooSmall) {
            addIssueToContext(ctx, {
              code: ZodIssueCode.too_small,
              minimum: check.value,
              type: "string",
              inclusive: true,
              exact: true,
              message: check.message
            });
          }
          status.dirty();
        }
      } else if (check.kind === "email") {
        if (!emailRegex.test(input.data)) {
          ctx = this._getOrReturnCtx(input, ctx);
          addIssueToContext(ctx, {
            validation: "email",
            code: ZodIssueCode.invalid_string,
            message: check.message
          });
          status.dirty();
        }
      } else if (check.kind === "emoji") {
        if (!emojiRegex$1) {
          emojiRegex$1 = new RegExp(_emojiRegex, "u");
        }
        if (!emojiRegex$1.test(input.data)) {
          ctx = this._getOrReturnCtx(input, ctx);
          addIssueToContext(ctx, {
            validation: "emoji",
            code: ZodIssueCode.invalid_string,
            message: check.message
          });
          status.dirty();
        }
      } else if (check.kind === "uuid") {
        if (!uuidRegex.test(input.data)) {
          ctx = this._getOrReturnCtx(input, ctx);
          addIssueToContext(ctx, {
            validation: "uuid",
            code: ZodIssueCode.invalid_string,
            message: check.message
          });
          status.dirty();
        }
      } else if (check.kind === "nanoid") {
        if (!nanoidRegex.test(input.data)) {
          ctx = this._getOrReturnCtx(input, ctx);
          addIssueToContext(ctx, {
            validation: "nanoid",
            code: ZodIssueCode.invalid_string,
            message: check.message
          });
          status.dirty();
        }
      } else if (check.kind === "cuid") {
        if (!cuidRegex.test(input.data)) {
          ctx = this._getOrReturnCtx(input, ctx);
          addIssueToContext(ctx, {
            validation: "cuid",
            code: ZodIssueCode.invalid_string,
            message: check.message
          });
          status.dirty();
        }
      } else if (check.kind === "cuid2") {
        if (!cuid2Regex.test(input.data)) {
          ctx = this._getOrReturnCtx(input, ctx);
          addIssueToContext(ctx, {
            validation: "cuid2",
            code: ZodIssueCode.invalid_string,
            message: check.message
          });
          status.dirty();
        }
      } else if (check.kind === "ulid") {
        if (!ulidRegex.test(input.data)) {
          ctx = this._getOrReturnCtx(input, ctx);
          addIssueToContext(ctx, {
            validation: "ulid",
            code: ZodIssueCode.invalid_string,
            message: check.message
          });
          status.dirty();
        }
      } else if (check.kind === "url") {
        try {
          new URL(input.data);
        } catch {
          ctx = this._getOrReturnCtx(input, ctx);
          addIssueToContext(ctx, {
            validation: "url",
            code: ZodIssueCode.invalid_string,
            message: check.message
          });
          status.dirty();
        }
      } else if (check.kind === "regex") {
        check.regex.lastIndex = 0;
        const testResult = check.regex.test(input.data);
        if (!testResult) {
          ctx = this._getOrReturnCtx(input, ctx);
          addIssueToContext(ctx, {
            validation: "regex",
            code: ZodIssueCode.invalid_string,
            message: check.message
          });
          status.dirty();
        }
      } else if (check.kind === "trim") {
        input.data = input.data.trim();
      } else if (check.kind === "includes") {
        if (!input.data.includes(check.value, check.position)) {
          ctx = this._getOrReturnCtx(input, ctx);
          addIssueToContext(ctx, {
            code: ZodIssueCode.invalid_string,
            validation: { includes: check.value, position: check.position },
            message: check.message
          });
          status.dirty();
        }
      } else if (check.kind === "toLowerCase") {
        input.data = input.data.toLowerCase();
      } else if (check.kind === "toUpperCase") {
        input.data = input.data.toUpperCase();
      } else if (check.kind === "startsWith") {
        if (!input.data.startsWith(check.value)) {
          ctx = this._getOrReturnCtx(input, ctx);
          addIssueToContext(ctx, {
            code: ZodIssueCode.invalid_string,
            validation: { startsWith: check.value },
            message: check.message
          });
          status.dirty();
        }
      } else if (check.kind === "endsWith") {
        if (!input.data.endsWith(check.value)) {
          ctx = this._getOrReturnCtx(input, ctx);
          addIssueToContext(ctx, {
            code: ZodIssueCode.invalid_string,
            validation: { endsWith: check.value },
            message: check.message
          });
          status.dirty();
        }
      } else if (check.kind === "datetime") {
        const regex = datetimeRegex(check);
        if (!regex.test(input.data)) {
          ctx = this._getOrReturnCtx(input, ctx);
          addIssueToContext(ctx, {
            code: ZodIssueCode.invalid_string,
            validation: "datetime",
            message: check.message
          });
          status.dirty();
        }
      } else if (check.kind === "date") {
        const regex = dateRegex;
        if (!regex.test(input.data)) {
          ctx = this._getOrReturnCtx(input, ctx);
          addIssueToContext(ctx, {
            code: ZodIssueCode.invalid_string,
            validation: "date",
            message: check.message
          });
          status.dirty();
        }
      } else if (check.kind === "time") {
        const regex = timeRegex(check);
        if (!regex.test(input.data)) {
          ctx = this._getOrReturnCtx(input, ctx);
          addIssueToContext(ctx, {
            code: ZodIssueCode.invalid_string,
            validation: "time",
            message: check.message
          });
          status.dirty();
        }
      } else if (check.kind === "duration") {
        if (!durationRegex.test(input.data)) {
          ctx = this._getOrReturnCtx(input, ctx);
          addIssueToContext(ctx, {
            validation: "duration",
            code: ZodIssueCode.invalid_string,
            message: check.message
          });
          status.dirty();
        }
      } else if (check.kind === "ip") {
        if (!isValidIP(input.data, check.version)) {
          ctx = this._getOrReturnCtx(input, ctx);
          addIssueToContext(ctx, {
            validation: "ip",
            code: ZodIssueCode.invalid_string,
            message: check.message
          });
          status.dirty();
        }
      } else if (check.kind === "jwt") {
        if (!isValidJWT(input.data, check.alg)) {
          ctx = this._getOrReturnCtx(input, ctx);
          addIssueToContext(ctx, {
            validation: "jwt",
            code: ZodIssueCode.invalid_string,
            message: check.message
          });
          status.dirty();
        }
      } else if (check.kind === "cidr") {
        if (!isValidCidr(input.data, check.version)) {
          ctx = this._getOrReturnCtx(input, ctx);
          addIssueToContext(ctx, {
            validation: "cidr",
            code: ZodIssueCode.invalid_string,
            message: check.message
          });
          status.dirty();
        }
      } else if (check.kind === "base64") {
        if (!base64Regex.test(input.data)) {
          ctx = this._getOrReturnCtx(input, ctx);
          addIssueToContext(ctx, {
            validation: "base64",
            code: ZodIssueCode.invalid_string,
            message: check.message
          });
          status.dirty();
        }
      } else if (check.kind === "base64url") {
        if (!base64urlRegex.test(input.data)) {
          ctx = this._getOrReturnCtx(input, ctx);
          addIssueToContext(ctx, {
            validation: "base64url",
            code: ZodIssueCode.invalid_string,
            message: check.message
          });
          status.dirty();
        }
      } else {
        util$1.assertNever(check);
      }
    }
    return { status: status.value, value: input.data };
  }
  _regex(regex, validation, message) {
    return this.refinement((data2) => regex.test(data2), {
      validation,
      code: ZodIssueCode.invalid_string,
      ...errorUtil.errToObj(message)
    });
  }
  _addCheck(check) {
    return new ZodString({
      ...this._def,
      checks: [...this._def.checks, check]
    });
  }
  email(message) {
    return this._addCheck({ kind: "email", ...errorUtil.errToObj(message) });
  }
  url(message) {
    return this._addCheck({ kind: "url", ...errorUtil.errToObj(message) });
  }
  emoji(message) {
    return this._addCheck({ kind: "emoji", ...errorUtil.errToObj(message) });
  }
  uuid(message) {
    return this._addCheck({ kind: "uuid", ...errorUtil.errToObj(message) });
  }
  nanoid(message) {
    return this._addCheck({ kind: "nanoid", ...errorUtil.errToObj(message) });
  }
  cuid(message) {
    return this._addCheck({ kind: "cuid", ...errorUtil.errToObj(message) });
  }
  cuid2(message) {
    return this._addCheck({ kind: "cuid2", ...errorUtil.errToObj(message) });
  }
  ulid(message) {
    return this._addCheck({ kind: "ulid", ...errorUtil.errToObj(message) });
  }
  base64(message) {
    return this._addCheck({ kind: "base64", ...errorUtil.errToObj(message) });
  }
  base64url(message) {
    return this._addCheck({
      kind: "base64url",
      ...errorUtil.errToObj(message)
    });
  }
  jwt(options) {
    return this._addCheck({ kind: "jwt", ...errorUtil.errToObj(options) });
  }
  ip(options) {
    return this._addCheck({ kind: "ip", ...errorUtil.errToObj(options) });
  }
  cidr(options) {
    return this._addCheck({ kind: "cidr", ...errorUtil.errToObj(options) });
  }
  datetime(options) {
    if (typeof options === "string") {
      return this._addCheck({
        kind: "datetime",
        precision: null,
        offset: false,
        local: false,
        message: options
      });
    }
    return this._addCheck({
      kind: "datetime",
      precision: typeof (options == null ? void 0 : options.precision) === "undefined" ? null : options == null ? void 0 : options.precision,
      offset: (options == null ? void 0 : options.offset) ?? false,
      local: (options == null ? void 0 : options.local) ?? false,
      ...errorUtil.errToObj(options == null ? void 0 : options.message)
    });
  }
  date(message) {
    return this._addCheck({ kind: "date", message });
  }
  time(options) {
    if (typeof options === "string") {
      return this._addCheck({
        kind: "time",
        precision: null,
        message: options
      });
    }
    return this._addCheck({
      kind: "time",
      precision: typeof (options == null ? void 0 : options.precision) === "undefined" ? null : options == null ? void 0 : options.precision,
      ...errorUtil.errToObj(options == null ? void 0 : options.message)
    });
  }
  duration(message) {
    return this._addCheck({ kind: "duration", ...errorUtil.errToObj(message) });
  }
  regex(regex, message) {
    return this._addCheck({
      kind: "regex",
      regex,
      ...errorUtil.errToObj(message)
    });
  }
  includes(value, options) {
    return this._addCheck({
      kind: "includes",
      value,
      position: options == null ? void 0 : options.position,
      ...errorUtil.errToObj(options == null ? void 0 : options.message)
    });
  }
  startsWith(value, message) {
    return this._addCheck({
      kind: "startsWith",
      value,
      ...errorUtil.errToObj(message)
    });
  }
  endsWith(value, message) {
    return this._addCheck({
      kind: "endsWith",
      value,
      ...errorUtil.errToObj(message)
    });
  }
  min(minLength, message) {
    return this._addCheck({
      kind: "min",
      value: minLength,
      ...errorUtil.errToObj(message)
    });
  }
  max(maxLength, message) {
    return this._addCheck({
      kind: "max",
      value: maxLength,
      ...errorUtil.errToObj(message)
    });
  }
  length(len, message) {
    return this._addCheck({
      kind: "length",
      value: len,
      ...errorUtil.errToObj(message)
    });
  }
  /**
   * Equivalent to `.min(1)`
   */
  nonempty(message) {
    return this.min(1, errorUtil.errToObj(message));
  }
  trim() {
    return new ZodString({
      ...this._def,
      checks: [...this._def.checks, { kind: "trim" }]
    });
  }
  toLowerCase() {
    return new ZodString({
      ...this._def,
      checks: [...this._def.checks, { kind: "toLowerCase" }]
    });
  }
  toUpperCase() {
    return new ZodString({
      ...this._def,
      checks: [...this._def.checks, { kind: "toUpperCase" }]
    });
  }
  get isDatetime() {
    return !!this._def.checks.find((ch) => ch.kind === "datetime");
  }
  get isDate() {
    return !!this._def.checks.find((ch) => ch.kind === "date");
  }
  get isTime() {
    return !!this._def.checks.find((ch) => ch.kind === "time");
  }
  get isDuration() {
    return !!this._def.checks.find((ch) => ch.kind === "duration");
  }
  get isEmail() {
    return !!this._def.checks.find((ch) => ch.kind === "email");
  }
  get isURL() {
    return !!this._def.checks.find((ch) => ch.kind === "url");
  }
  get isEmoji() {
    return !!this._def.checks.find((ch) => ch.kind === "emoji");
  }
  get isUUID() {
    return !!this._def.checks.find((ch) => ch.kind === "uuid");
  }
  get isNANOID() {
    return !!this._def.checks.find((ch) => ch.kind === "nanoid");
  }
  get isCUID() {
    return !!this._def.checks.find((ch) => ch.kind === "cuid");
  }
  get isCUID2() {
    return !!this._def.checks.find((ch) => ch.kind === "cuid2");
  }
  get isULID() {
    return !!this._def.checks.find((ch) => ch.kind === "ulid");
  }
  get isIP() {
    return !!this._def.checks.find((ch) => ch.kind === "ip");
  }
  get isCIDR() {
    return !!this._def.checks.find((ch) => ch.kind === "cidr");
  }
  get isBase64() {
    return !!this._def.checks.find((ch) => ch.kind === "base64");
  }
  get isBase64url() {
    return !!this._def.checks.find((ch) => ch.kind === "base64url");
  }
  get minLength() {
    let min = null;
    for (const ch of this._def.checks) {
      if (ch.kind === "min") {
        if (min === null || ch.value > min)
          min = ch.value;
      }
    }
    return min;
  }
  get maxLength() {
    let max = null;
    for (const ch of this._def.checks) {
      if (ch.kind === "max") {
        if (max === null || ch.value < max)
          max = ch.value;
      }
    }
    return max;
  }
}
ZodString.create = (params) => {
  return new ZodString({
    checks: [],
    typeName: ZodFirstPartyTypeKind.ZodString,
    coerce: (params == null ? void 0 : params.coerce) ?? false,
    ...processCreateParams$1(params)
  });
};
function floatSafeRemainder(val, step) {
  const valDecCount = (val.toString().split(".")[1] || "").length;
  const stepDecCount = (step.toString().split(".")[1] || "").length;
  const decCount = valDecCount > stepDecCount ? valDecCount : stepDecCount;
  const valInt = Number.parseInt(val.toFixed(decCount).replace(".", ""));
  const stepInt = Number.parseInt(step.toFixed(decCount).replace(".", ""));
  return valInt % stepInt / 10 ** decCount;
}
class ZodNumber extends ZodType {
  constructor() {
    super(...arguments);
    this.min = this.gte;
    this.max = this.lte;
    this.step = this.multipleOf;
  }
  _parse(input) {
    if (this._def.coerce) {
      input.data = Number(input.data);
    }
    const parsedType = this._getType(input);
    if (parsedType !== ZodParsedType.number) {
      const ctx2 = this._getOrReturnCtx(input);
      addIssueToContext(ctx2, {
        code: ZodIssueCode.invalid_type,
        expected: ZodParsedType.number,
        received: ctx2.parsedType
      });
      return INVALID;
    }
    let ctx = void 0;
    const status = new ParseStatus();
    for (const check of this._def.checks) {
      if (check.kind === "int") {
        if (!util$1.isInteger(input.data)) {
          ctx = this._getOrReturnCtx(input, ctx);
          addIssueToContext(ctx, {
            code: ZodIssueCode.invalid_type,
            expected: "integer",
            received: "float",
            message: check.message
          });
          status.dirty();
        }
      } else if (check.kind === "min") {
        const tooSmall = check.inclusive ? input.data < check.value : input.data <= check.value;
        if (tooSmall) {
          ctx = this._getOrReturnCtx(input, ctx);
          addIssueToContext(ctx, {
            code: ZodIssueCode.too_small,
            minimum: check.value,
            type: "number",
            inclusive: check.inclusive,
            exact: false,
            message: check.message
          });
          status.dirty();
        }
      } else if (check.kind === "max") {
        const tooBig = check.inclusive ? input.data > check.value : input.data >= check.value;
        if (tooBig) {
          ctx = this._getOrReturnCtx(input, ctx);
          addIssueToContext(ctx, {
            code: ZodIssueCode.too_big,
            maximum: check.value,
            type: "number",
            inclusive: check.inclusive,
            exact: false,
            message: check.message
          });
          status.dirty();
        }
      } else if (check.kind === "multipleOf") {
        if (floatSafeRemainder(input.data, check.value) !== 0) {
          ctx = this._getOrReturnCtx(input, ctx);
          addIssueToContext(ctx, {
            code: ZodIssueCode.not_multiple_of,
            multipleOf: check.value,
            message: check.message
          });
          status.dirty();
        }
      } else if (check.kind === "finite") {
        if (!Number.isFinite(input.data)) {
          ctx = this._getOrReturnCtx(input, ctx);
          addIssueToContext(ctx, {
            code: ZodIssueCode.not_finite,
            message: check.message
          });
          status.dirty();
        }
      } else {
        util$1.assertNever(check);
      }
    }
    return { status: status.value, value: input.data };
  }
  gte(value, message) {
    return this.setLimit("min", value, true, errorUtil.toString(message));
  }
  gt(value, message) {
    return this.setLimit("min", value, false, errorUtil.toString(message));
  }
  lte(value, message) {
    return this.setLimit("max", value, true, errorUtil.toString(message));
  }
  lt(value, message) {
    return this.setLimit("max", value, false, errorUtil.toString(message));
  }
  setLimit(kind, value, inclusive, message) {
    return new ZodNumber({
      ...this._def,
      checks: [
        ...this._def.checks,
        {
          kind,
          value,
          inclusive,
          message: errorUtil.toString(message)
        }
      ]
    });
  }
  _addCheck(check) {
    return new ZodNumber({
      ...this._def,
      checks: [...this._def.checks, check]
    });
  }
  int(message) {
    return this._addCheck({
      kind: "int",
      message: errorUtil.toString(message)
    });
  }
  positive(message) {
    return this._addCheck({
      kind: "min",
      value: 0,
      inclusive: false,
      message: errorUtil.toString(message)
    });
  }
  negative(message) {
    return this._addCheck({
      kind: "max",
      value: 0,
      inclusive: false,
      message: errorUtil.toString(message)
    });
  }
  nonpositive(message) {
    return this._addCheck({
      kind: "max",
      value: 0,
      inclusive: true,
      message: errorUtil.toString(message)
    });
  }
  nonnegative(message) {
    return this._addCheck({
      kind: "min",
      value: 0,
      inclusive: true,
      message: errorUtil.toString(message)
    });
  }
  multipleOf(value, message) {
    return this._addCheck({
      kind: "multipleOf",
      value,
      message: errorUtil.toString(message)
    });
  }
  finite(message) {
    return this._addCheck({
      kind: "finite",
      message: errorUtil.toString(message)
    });
  }
  safe(message) {
    return this._addCheck({
      kind: "min",
      inclusive: true,
      value: Number.MIN_SAFE_INTEGER,
      message: errorUtil.toString(message)
    })._addCheck({
      kind: "max",
      inclusive: true,
      value: Number.MAX_SAFE_INTEGER,
      message: errorUtil.toString(message)
    });
  }
  get minValue() {
    let min = null;
    for (const ch of this._def.checks) {
      if (ch.kind === "min") {
        if (min === null || ch.value > min)
          min = ch.value;
      }
    }
    return min;
  }
  get maxValue() {
    let max = null;
    for (const ch of this._def.checks) {
      if (ch.kind === "max") {
        if (max === null || ch.value < max)
          max = ch.value;
      }
    }
    return max;
  }
  get isInt() {
    return !!this._def.checks.find((ch) => ch.kind === "int" || ch.kind === "multipleOf" && util$1.isInteger(ch.value));
  }
  get isFinite() {
    let max = null;
    let min = null;
    for (const ch of this._def.checks) {
      if (ch.kind === "finite" || ch.kind === "int" || ch.kind === "multipleOf") {
        return true;
      } else if (ch.kind === "min") {
        if (min === null || ch.value > min)
          min = ch.value;
      } else if (ch.kind === "max") {
        if (max === null || ch.value < max)
          max = ch.value;
      }
    }
    return Number.isFinite(min) && Number.isFinite(max);
  }
}
ZodNumber.create = (params) => {
  return new ZodNumber({
    checks: [],
    typeName: ZodFirstPartyTypeKind.ZodNumber,
    coerce: (params == null ? void 0 : params.coerce) || false,
    ...processCreateParams$1(params)
  });
};
class ZodBigInt extends ZodType {
  constructor() {
    super(...arguments);
    this.min = this.gte;
    this.max = this.lte;
  }
  _parse(input) {
    if (this._def.coerce) {
      try {
        input.data = BigInt(input.data);
      } catch {
        return this._getInvalidInput(input);
      }
    }
    const parsedType = this._getType(input);
    if (parsedType !== ZodParsedType.bigint) {
      return this._getInvalidInput(input);
    }
    let ctx = void 0;
    const status = new ParseStatus();
    for (const check of this._def.checks) {
      if (check.kind === "min") {
        const tooSmall = check.inclusive ? input.data < check.value : input.data <= check.value;
        if (tooSmall) {
          ctx = this._getOrReturnCtx(input, ctx);
          addIssueToContext(ctx, {
            code: ZodIssueCode.too_small,
            type: "bigint",
            minimum: check.value,
            inclusive: check.inclusive,
            message: check.message
          });
          status.dirty();
        }
      } else if (check.kind === "max") {
        const tooBig = check.inclusive ? input.data > check.value : input.data >= check.value;
        if (tooBig) {
          ctx = this._getOrReturnCtx(input, ctx);
          addIssueToContext(ctx, {
            code: ZodIssueCode.too_big,
            type: "bigint",
            maximum: check.value,
            inclusive: check.inclusive,
            message: check.message
          });
          status.dirty();
        }
      } else if (check.kind === "multipleOf") {
        if (input.data % check.value !== BigInt(0)) {
          ctx = this._getOrReturnCtx(input, ctx);
          addIssueToContext(ctx, {
            code: ZodIssueCode.not_multiple_of,
            multipleOf: check.value,
            message: check.message
          });
          status.dirty();
        }
      } else {
        util$1.assertNever(check);
      }
    }
    return { status: status.value, value: input.data };
  }
  _getInvalidInput(input) {
    const ctx = this._getOrReturnCtx(input);
    addIssueToContext(ctx, {
      code: ZodIssueCode.invalid_type,
      expected: ZodParsedType.bigint,
      received: ctx.parsedType
    });
    return INVALID;
  }
  gte(value, message) {
    return this.setLimit("min", value, true, errorUtil.toString(message));
  }
  gt(value, message) {
    return this.setLimit("min", value, false, errorUtil.toString(message));
  }
  lte(value, message) {
    return this.setLimit("max", value, true, errorUtil.toString(message));
  }
  lt(value, message) {
    return this.setLimit("max", value, false, errorUtil.toString(message));
  }
  setLimit(kind, value, inclusive, message) {
    return new ZodBigInt({
      ...this._def,
      checks: [
        ...this._def.checks,
        {
          kind,
          value,
          inclusive,
          message: errorUtil.toString(message)
        }
      ]
    });
  }
  _addCheck(check) {
    return new ZodBigInt({
      ...this._def,
      checks: [...this._def.checks, check]
    });
  }
  positive(message) {
    return this._addCheck({
      kind: "min",
      value: BigInt(0),
      inclusive: false,
      message: errorUtil.toString(message)
    });
  }
  negative(message) {
    return this._addCheck({
      kind: "max",
      value: BigInt(0),
      inclusive: false,
      message: errorUtil.toString(message)
    });
  }
  nonpositive(message) {
    return this._addCheck({
      kind: "max",
      value: BigInt(0),
      inclusive: true,
      message: errorUtil.toString(message)
    });
  }
  nonnegative(message) {
    return this._addCheck({
      kind: "min",
      value: BigInt(0),
      inclusive: true,
      message: errorUtil.toString(message)
    });
  }
  multipleOf(value, message) {
    return this._addCheck({
      kind: "multipleOf",
      value,
      message: errorUtil.toString(message)
    });
  }
  get minValue() {
    let min = null;
    for (const ch of this._def.checks) {
      if (ch.kind === "min") {
        if (min === null || ch.value > min)
          min = ch.value;
      }
    }
    return min;
  }
  get maxValue() {
    let max = null;
    for (const ch of this._def.checks) {
      if (ch.kind === "max") {
        if (max === null || ch.value < max)
          max = ch.value;
      }
    }
    return max;
  }
}
ZodBigInt.create = (params) => {
  return new ZodBigInt({
    checks: [],
    typeName: ZodFirstPartyTypeKind.ZodBigInt,
    coerce: (params == null ? void 0 : params.coerce) ?? false,
    ...processCreateParams$1(params)
  });
};
class ZodBoolean extends ZodType {
  _parse(input) {
    if (this._def.coerce) {
      input.data = Boolean(input.data);
    }
    const parsedType = this._getType(input);
    if (parsedType !== ZodParsedType.boolean) {
      const ctx = this._getOrReturnCtx(input);
      addIssueToContext(ctx, {
        code: ZodIssueCode.invalid_type,
        expected: ZodParsedType.boolean,
        received: ctx.parsedType
      });
      return INVALID;
    }
    return OK(input.data);
  }
}
ZodBoolean.create = (params) => {
  return new ZodBoolean({
    typeName: ZodFirstPartyTypeKind.ZodBoolean,
    coerce: (params == null ? void 0 : params.coerce) || false,
    ...processCreateParams$1(params)
  });
};
class ZodDate extends ZodType {
  _parse(input) {
    if (this._def.coerce) {
      input.data = new Date(input.data);
    }
    const parsedType = this._getType(input);
    if (parsedType !== ZodParsedType.date) {
      const ctx2 = this._getOrReturnCtx(input);
      addIssueToContext(ctx2, {
        code: ZodIssueCode.invalid_type,
        expected: ZodParsedType.date,
        received: ctx2.parsedType
      });
      return INVALID;
    }
    if (Number.isNaN(input.data.getTime())) {
      const ctx2 = this._getOrReturnCtx(input);
      addIssueToContext(ctx2, {
        code: ZodIssueCode.invalid_date
      });
      return INVALID;
    }
    const status = new ParseStatus();
    let ctx = void 0;
    for (const check of this._def.checks) {
      if (check.kind === "min") {
        if (input.data.getTime() < check.value) {
          ctx = this._getOrReturnCtx(input, ctx);
          addIssueToContext(ctx, {
            code: ZodIssueCode.too_small,
            message: check.message,
            inclusive: true,
            exact: false,
            minimum: check.value,
            type: "date"
          });
          status.dirty();
        }
      } else if (check.kind === "max") {
        if (input.data.getTime() > check.value) {
          ctx = this._getOrReturnCtx(input, ctx);
          addIssueToContext(ctx, {
            code: ZodIssueCode.too_big,
            message: check.message,
            inclusive: true,
            exact: false,
            maximum: check.value,
            type: "date"
          });
          status.dirty();
        }
      } else {
        util$1.assertNever(check);
      }
    }
    return {
      status: status.value,
      value: new Date(input.data.getTime())
    };
  }
  _addCheck(check) {
    return new ZodDate({
      ...this._def,
      checks: [...this._def.checks, check]
    });
  }
  min(minDate, message) {
    return this._addCheck({
      kind: "min",
      value: minDate.getTime(),
      message: errorUtil.toString(message)
    });
  }
  max(maxDate, message) {
    return this._addCheck({
      kind: "max",
      value: maxDate.getTime(),
      message: errorUtil.toString(message)
    });
  }
  get minDate() {
    let min = null;
    for (const ch of this._def.checks) {
      if (ch.kind === "min") {
        if (min === null || ch.value > min)
          min = ch.value;
      }
    }
    return min != null ? new Date(min) : null;
  }
  get maxDate() {
    let max = null;
    for (const ch of this._def.checks) {
      if (ch.kind === "max") {
        if (max === null || ch.value < max)
          max = ch.value;
      }
    }
    return max != null ? new Date(max) : null;
  }
}
ZodDate.create = (params) => {
  return new ZodDate({
    checks: [],
    coerce: (params == null ? void 0 : params.coerce) || false,
    typeName: ZodFirstPartyTypeKind.ZodDate,
    ...processCreateParams$1(params)
  });
};
class ZodSymbol extends ZodType {
  _parse(input) {
    const parsedType = this._getType(input);
    if (parsedType !== ZodParsedType.symbol) {
      const ctx = this._getOrReturnCtx(input);
      addIssueToContext(ctx, {
        code: ZodIssueCode.invalid_type,
        expected: ZodParsedType.symbol,
        received: ctx.parsedType
      });
      return INVALID;
    }
    return OK(input.data);
  }
}
ZodSymbol.create = (params) => {
  return new ZodSymbol({
    typeName: ZodFirstPartyTypeKind.ZodSymbol,
    ...processCreateParams$1(params)
  });
};
class ZodUndefined extends ZodType {
  _parse(input) {
    const parsedType = this._getType(input);
    if (parsedType !== ZodParsedType.undefined) {
      const ctx = this._getOrReturnCtx(input);
      addIssueToContext(ctx, {
        code: ZodIssueCode.invalid_type,
        expected: ZodParsedType.undefined,
        received: ctx.parsedType
      });
      return INVALID;
    }
    return OK(input.data);
  }
}
ZodUndefined.create = (params) => {
  return new ZodUndefined({
    typeName: ZodFirstPartyTypeKind.ZodUndefined,
    ...processCreateParams$1(params)
  });
};
class ZodNull extends ZodType {
  _parse(input) {
    const parsedType = this._getType(input);
    if (parsedType !== ZodParsedType.null) {
      const ctx = this._getOrReturnCtx(input);
      addIssueToContext(ctx, {
        code: ZodIssueCode.invalid_type,
        expected: ZodParsedType.null,
        received: ctx.parsedType
      });
      return INVALID;
    }
    return OK(input.data);
  }
}
ZodNull.create = (params) => {
  return new ZodNull({
    typeName: ZodFirstPartyTypeKind.ZodNull,
    ...processCreateParams$1(params)
  });
};
class ZodAny extends ZodType {
  constructor() {
    super(...arguments);
    this._any = true;
  }
  _parse(input) {
    return OK(input.data);
  }
}
ZodAny.create = (params) => {
  return new ZodAny({
    typeName: ZodFirstPartyTypeKind.ZodAny,
    ...processCreateParams$1(params)
  });
};
class ZodUnknown extends ZodType {
  constructor() {
    super(...arguments);
    this._unknown = true;
  }
  _parse(input) {
    return OK(input.data);
  }
}
ZodUnknown.create = (params) => {
  return new ZodUnknown({
    typeName: ZodFirstPartyTypeKind.ZodUnknown,
    ...processCreateParams$1(params)
  });
};
class ZodNever extends ZodType {
  _parse(input) {
    const ctx = this._getOrReturnCtx(input);
    addIssueToContext(ctx, {
      code: ZodIssueCode.invalid_type,
      expected: ZodParsedType.never,
      received: ctx.parsedType
    });
    return INVALID;
  }
}
ZodNever.create = (params) => {
  return new ZodNever({
    typeName: ZodFirstPartyTypeKind.ZodNever,
    ...processCreateParams$1(params)
  });
};
class ZodVoid extends ZodType {
  _parse(input) {
    const parsedType = this._getType(input);
    if (parsedType !== ZodParsedType.undefined) {
      const ctx = this._getOrReturnCtx(input);
      addIssueToContext(ctx, {
        code: ZodIssueCode.invalid_type,
        expected: ZodParsedType.void,
        received: ctx.parsedType
      });
      return INVALID;
    }
    return OK(input.data);
  }
}
ZodVoid.create = (params) => {
  return new ZodVoid({
    typeName: ZodFirstPartyTypeKind.ZodVoid,
    ...processCreateParams$1(params)
  });
};
class ZodArray extends ZodType {
  _parse(input) {
    const { ctx, status } = this._processInputParams(input);
    const def = this._def;
    if (ctx.parsedType !== ZodParsedType.array) {
      addIssueToContext(ctx, {
        code: ZodIssueCode.invalid_type,
        expected: ZodParsedType.array,
        received: ctx.parsedType
      });
      return INVALID;
    }
    if (def.exactLength !== null) {
      const tooBig = ctx.data.length > def.exactLength.value;
      const tooSmall = ctx.data.length < def.exactLength.value;
      if (tooBig || tooSmall) {
        addIssueToContext(ctx, {
          code: tooBig ? ZodIssueCode.too_big : ZodIssueCode.too_small,
          minimum: tooSmall ? def.exactLength.value : void 0,
          maximum: tooBig ? def.exactLength.value : void 0,
          type: "array",
          inclusive: true,
          exact: true,
          message: def.exactLength.message
        });
        status.dirty();
      }
    }
    if (def.minLength !== null) {
      if (ctx.data.length < def.minLength.value) {
        addIssueToContext(ctx, {
          code: ZodIssueCode.too_small,
          minimum: def.minLength.value,
          type: "array",
          inclusive: true,
          exact: false,
          message: def.minLength.message
        });
        status.dirty();
      }
    }
    if (def.maxLength !== null) {
      if (ctx.data.length > def.maxLength.value) {
        addIssueToContext(ctx, {
          code: ZodIssueCode.too_big,
          maximum: def.maxLength.value,
          type: "array",
          inclusive: true,
          exact: false,
          message: def.maxLength.message
        });
        status.dirty();
      }
    }
    if (ctx.common.async) {
      return Promise.all([...ctx.data].map((item, i2) => {
        return def.type._parseAsync(new ParseInputLazyPath(ctx, item, ctx.path, i2));
      })).then((result2) => {
        return ParseStatus.mergeArray(status, result2);
      });
    }
    const result = [...ctx.data].map((item, i2) => {
      return def.type._parseSync(new ParseInputLazyPath(ctx, item, ctx.path, i2));
    });
    return ParseStatus.mergeArray(status, result);
  }
  get element() {
    return this._def.type;
  }
  min(minLength, message) {
    return new ZodArray({
      ...this._def,
      minLength: { value: minLength, message: errorUtil.toString(message) }
    });
  }
  max(maxLength, message) {
    return new ZodArray({
      ...this._def,
      maxLength: { value: maxLength, message: errorUtil.toString(message) }
    });
  }
  length(len, message) {
    return new ZodArray({
      ...this._def,
      exactLength: { value: len, message: errorUtil.toString(message) }
    });
  }
  nonempty(message) {
    return this.min(1, message);
  }
}
ZodArray.create = (schema, params) => {
  return new ZodArray({
    type: schema,
    minLength: null,
    maxLength: null,
    exactLength: null,
    typeName: ZodFirstPartyTypeKind.ZodArray,
    ...processCreateParams$1(params)
  });
};
function deepPartialify(schema) {
  if (schema instanceof ZodObject) {
    const newShape = {};
    for (const key in schema.shape) {
      const fieldSchema = schema.shape[key];
      newShape[key] = ZodOptional.create(deepPartialify(fieldSchema));
    }
    return new ZodObject({
      ...schema._def,
      shape: () => newShape
    });
  } else if (schema instanceof ZodArray) {
    return new ZodArray({
      ...schema._def,
      type: deepPartialify(schema.element)
    });
  } else if (schema instanceof ZodOptional) {
    return ZodOptional.create(deepPartialify(schema.unwrap()));
  } else if (schema instanceof ZodNullable) {
    return ZodNullable.create(deepPartialify(schema.unwrap()));
  } else if (schema instanceof ZodTuple) {
    return ZodTuple.create(schema.items.map((item) => deepPartialify(item)));
  } else {
    return schema;
  }
}
class ZodObject extends ZodType {
  constructor() {
    super(...arguments);
    this._cached = null;
    this.nonstrict = this.passthrough;
    this.augment = this.extend;
  }
  _getCached() {
    if (this._cached !== null)
      return this._cached;
    const shape = this._def.shape();
    const keys = util$1.objectKeys(shape);
    this._cached = { shape, keys };
    return this._cached;
  }
  _parse(input) {
    const parsedType = this._getType(input);
    if (parsedType !== ZodParsedType.object) {
      const ctx2 = this._getOrReturnCtx(input);
      addIssueToContext(ctx2, {
        code: ZodIssueCode.invalid_type,
        expected: ZodParsedType.object,
        received: ctx2.parsedType
      });
      return INVALID;
    }
    const { status, ctx } = this._processInputParams(input);
    const { shape, keys: shapeKeys } = this._getCached();
    const extraKeys = [];
    if (!(this._def.catchall instanceof ZodNever && this._def.unknownKeys === "strip")) {
      for (const key in ctx.data) {
        if (!shapeKeys.includes(key)) {
          extraKeys.push(key);
        }
      }
    }
    const pairs = [];
    for (const key of shapeKeys) {
      const keyValidator = shape[key];
      const value = ctx.data[key];
      pairs.push({
        key: { status: "valid", value: key },
        value: keyValidator._parse(new ParseInputLazyPath(ctx, value, ctx.path, key)),
        alwaysSet: key in ctx.data
      });
    }
    if (this._def.catchall instanceof ZodNever) {
      const unknownKeys = this._def.unknownKeys;
      if (unknownKeys === "passthrough") {
        for (const key of extraKeys) {
          pairs.push({
            key: { status: "valid", value: key },
            value: { status: "valid", value: ctx.data[key] }
          });
        }
      } else if (unknownKeys === "strict") {
        if (extraKeys.length > 0) {
          addIssueToContext(ctx, {
            code: ZodIssueCode.unrecognized_keys,
            keys: extraKeys
          });
          status.dirty();
        }
      } else if (unknownKeys === "strip") ;
      else {
        throw new Error(`Internal ZodObject error: invalid unknownKeys value.`);
      }
    } else {
      const catchall = this._def.catchall;
      for (const key of extraKeys) {
        const value = ctx.data[key];
        pairs.push({
          key: { status: "valid", value: key },
          value: catchall._parse(
            new ParseInputLazyPath(ctx, value, ctx.path, key)
            //, ctx.child(key), value, getParsedType(value)
          ),
          alwaysSet: key in ctx.data
        });
      }
    }
    if (ctx.common.async) {
      return Promise.resolve().then(async () => {
        const syncPairs = [];
        for (const pair of pairs) {
          const key = await pair.key;
          const value = await pair.value;
          syncPairs.push({
            key,
            value,
            alwaysSet: pair.alwaysSet
          });
        }
        return syncPairs;
      }).then((syncPairs) => {
        return ParseStatus.mergeObjectSync(status, syncPairs);
      });
    } else {
      return ParseStatus.mergeObjectSync(status, pairs);
    }
  }
  get shape() {
    return this._def.shape();
  }
  strict(message) {
    errorUtil.errToObj;
    return new ZodObject({
      ...this._def,
      unknownKeys: "strict",
      ...message !== void 0 ? {
        errorMap: (issue, ctx) => {
          var _a, _b;
          const defaultError = ((_b = (_a = this._def).errorMap) == null ? void 0 : _b.call(_a, issue, ctx).message) ?? ctx.defaultError;
          if (issue.code === "unrecognized_keys")
            return {
              message: errorUtil.errToObj(message).message ?? defaultError
            };
          return {
            message: defaultError
          };
        }
      } : {}
    });
  }
  strip() {
    return new ZodObject({
      ...this._def,
      unknownKeys: "strip"
    });
  }
  passthrough() {
    return new ZodObject({
      ...this._def,
      unknownKeys: "passthrough"
    });
  }
  // const AugmentFactory =
  //   <Def extends ZodObjectDef>(def: Def) =>
  //   <Augmentation extends ZodRawShape>(
  //     augmentation: Augmentation
  //   ): ZodObject<
  //     extendShape<ReturnType<Def["shape"]>, Augmentation>,
  //     Def["unknownKeys"],
  //     Def["catchall"]
  //   > => {
  //     return new ZodObject({
  //       ...def,
  //       shape: () => ({
  //         ...def.shape(),
  //         ...augmentation,
  //       }),
  //     }) as any;
  //   };
  extend(augmentation) {
    return new ZodObject({
      ...this._def,
      shape: () => ({
        ...this._def.shape(),
        ...augmentation
      })
    });
  }
  /**
   * Prior to zod@1.0.12 there was a bug in the
   * inferred type of merged objects. Please
   * upgrade if you are experiencing issues.
   */
  merge(merging) {
    const merged = new ZodObject({
      unknownKeys: merging._def.unknownKeys,
      catchall: merging._def.catchall,
      shape: () => ({
        ...this._def.shape(),
        ...merging._def.shape()
      }),
      typeName: ZodFirstPartyTypeKind.ZodObject
    });
    return merged;
  }
  // merge<
  //   Incoming extends AnyZodObject,
  //   Augmentation extends Incoming["shape"],
  //   NewOutput extends {
  //     [k in keyof Augmentation | keyof Output]: k extends keyof Augmentation
  //       ? Augmentation[k]["_output"]
  //       : k extends keyof Output
  //       ? Output[k]
  //       : never;
  //   },
  //   NewInput extends {
  //     [k in keyof Augmentation | keyof Input]: k extends keyof Augmentation
  //       ? Augmentation[k]["_input"]
  //       : k extends keyof Input
  //       ? Input[k]
  //       : never;
  //   }
  // >(
  //   merging: Incoming
  // ): ZodObject<
  //   extendShape<T, ReturnType<Incoming["_def"]["shape"]>>,
  //   Incoming["_def"]["unknownKeys"],
  //   Incoming["_def"]["catchall"],
  //   NewOutput,
  //   NewInput
  // > {
  //   const merged: any = new ZodObject({
  //     unknownKeys: merging._def.unknownKeys,
  //     catchall: merging._def.catchall,
  //     shape: () =>
  //       objectUtil.mergeShapes(this._def.shape(), merging._def.shape()),
  //     typeName: ZodFirstPartyTypeKind.ZodObject,
  //   }) as any;
  //   return merged;
  // }
  setKey(key, schema) {
    return this.augment({ [key]: schema });
  }
  // merge<Incoming extends AnyZodObject>(
  //   merging: Incoming
  // ): //ZodObject<T & Incoming["_shape"], UnknownKeys, Catchall> = (merging) => {
  // ZodObject<
  //   extendShape<T, ReturnType<Incoming["_def"]["shape"]>>,
  //   Incoming["_def"]["unknownKeys"],
  //   Incoming["_def"]["catchall"]
  // > {
  //   // const mergedShape = objectUtil.mergeShapes(
  //   //   this._def.shape(),
  //   //   merging._def.shape()
  //   // );
  //   const merged: any = new ZodObject({
  //     unknownKeys: merging._def.unknownKeys,
  //     catchall: merging._def.catchall,
  //     shape: () =>
  //       objectUtil.mergeShapes(this._def.shape(), merging._def.shape()),
  //     typeName: ZodFirstPartyTypeKind.ZodObject,
  //   }) as any;
  //   return merged;
  // }
  catchall(index) {
    return new ZodObject({
      ...this._def,
      catchall: index
    });
  }
  pick(mask) {
    const shape = {};
    for (const key of util$1.objectKeys(mask)) {
      if (mask[key] && this.shape[key]) {
        shape[key] = this.shape[key];
      }
    }
    return new ZodObject({
      ...this._def,
      shape: () => shape
    });
  }
  omit(mask) {
    const shape = {};
    for (const key of util$1.objectKeys(this.shape)) {
      if (!mask[key]) {
        shape[key] = this.shape[key];
      }
    }
    return new ZodObject({
      ...this._def,
      shape: () => shape
    });
  }
  /**
   * @deprecated
   */
  deepPartial() {
    return deepPartialify(this);
  }
  partial(mask) {
    const newShape = {};
    for (const key of util$1.objectKeys(this.shape)) {
      const fieldSchema = this.shape[key];
      if (mask && !mask[key]) {
        newShape[key] = fieldSchema;
      } else {
        newShape[key] = fieldSchema.optional();
      }
    }
    return new ZodObject({
      ...this._def,
      shape: () => newShape
    });
  }
  required(mask) {
    const newShape = {};
    for (const key of util$1.objectKeys(this.shape)) {
      if (mask && !mask[key]) {
        newShape[key] = this.shape[key];
      } else {
        const fieldSchema = this.shape[key];
        let newField = fieldSchema;
        while (newField instanceof ZodOptional) {
          newField = newField._def.innerType;
        }
        newShape[key] = newField;
      }
    }
    return new ZodObject({
      ...this._def,
      shape: () => newShape
    });
  }
  keyof() {
    return createZodEnum(util$1.objectKeys(this.shape));
  }
}
ZodObject.create = (shape, params) => {
  return new ZodObject({
    shape: () => shape,
    unknownKeys: "strip",
    catchall: ZodNever.create(),
    typeName: ZodFirstPartyTypeKind.ZodObject,
    ...processCreateParams$1(params)
  });
};
ZodObject.strictCreate = (shape, params) => {
  return new ZodObject({
    shape: () => shape,
    unknownKeys: "strict",
    catchall: ZodNever.create(),
    typeName: ZodFirstPartyTypeKind.ZodObject,
    ...processCreateParams$1(params)
  });
};
ZodObject.lazycreate = (shape, params) => {
  return new ZodObject({
    shape,
    unknownKeys: "strip",
    catchall: ZodNever.create(),
    typeName: ZodFirstPartyTypeKind.ZodObject,
    ...processCreateParams$1(params)
  });
};
class ZodUnion extends ZodType {
  _parse(input) {
    const { ctx } = this._processInputParams(input);
    const options = this._def.options;
    function handleResults(results) {
      for (const result of results) {
        if (result.result.status === "valid") {
          return result.result;
        }
      }
      for (const result of results) {
        if (result.result.status === "dirty") {
          ctx.common.issues.push(...result.ctx.common.issues);
          return result.result;
        }
      }
      const unionErrors = results.map((result) => new ZodError(result.ctx.common.issues));
      addIssueToContext(ctx, {
        code: ZodIssueCode.invalid_union,
        unionErrors
      });
      return INVALID;
    }
    if (ctx.common.async) {
      return Promise.all(options.map(async (option) => {
        const childCtx = {
          ...ctx,
          common: {
            ...ctx.common,
            issues: []
          },
          parent: null
        };
        return {
          result: await option._parseAsync({
            data: ctx.data,
            path: ctx.path,
            parent: childCtx
          }),
          ctx: childCtx
        };
      })).then(handleResults);
    } else {
      let dirty = void 0;
      const issues = [];
      for (const option of options) {
        const childCtx = {
          ...ctx,
          common: {
            ...ctx.common,
            issues: []
          },
          parent: null
        };
        const result = option._parseSync({
          data: ctx.data,
          path: ctx.path,
          parent: childCtx
        });
        if (result.status === "valid") {
          return result;
        } else if (result.status === "dirty" && !dirty) {
          dirty = { result, ctx: childCtx };
        }
        if (childCtx.common.issues.length) {
          issues.push(childCtx.common.issues);
        }
      }
      if (dirty) {
        ctx.common.issues.push(...dirty.ctx.common.issues);
        return dirty.result;
      }
      const unionErrors = issues.map((issues2) => new ZodError(issues2));
      addIssueToContext(ctx, {
        code: ZodIssueCode.invalid_union,
        unionErrors
      });
      return INVALID;
    }
  }
  get options() {
    return this._def.options;
  }
}
ZodUnion.create = (types, params) => {
  return new ZodUnion({
    options: types,
    typeName: ZodFirstPartyTypeKind.ZodUnion,
    ...processCreateParams$1(params)
  });
};
const getDiscriminator = (type2) => {
  if (type2 instanceof ZodLazy) {
    return getDiscriminator(type2.schema);
  } else if (type2 instanceof ZodEffects) {
    return getDiscriminator(type2.innerType());
  } else if (type2 instanceof ZodLiteral) {
    return [type2.value];
  } else if (type2 instanceof ZodEnum) {
    return type2.options;
  } else if (type2 instanceof ZodNativeEnum) {
    return util$1.objectValues(type2.enum);
  } else if (type2 instanceof ZodDefault) {
    return getDiscriminator(type2._def.innerType);
  } else if (type2 instanceof ZodUndefined) {
    return [void 0];
  } else if (type2 instanceof ZodNull) {
    return [null];
  } else if (type2 instanceof ZodOptional) {
    return [void 0, ...getDiscriminator(type2.unwrap())];
  } else if (type2 instanceof ZodNullable) {
    return [null, ...getDiscriminator(type2.unwrap())];
  } else if (type2 instanceof ZodBranded) {
    return getDiscriminator(type2.unwrap());
  } else if (type2 instanceof ZodReadonly) {
    return getDiscriminator(type2.unwrap());
  } else if (type2 instanceof ZodCatch) {
    return getDiscriminator(type2._def.innerType);
  } else {
    return [];
  }
};
class ZodDiscriminatedUnion extends ZodType {
  _parse(input) {
    const { ctx } = this._processInputParams(input);
    if (ctx.parsedType !== ZodParsedType.object) {
      addIssueToContext(ctx, {
        code: ZodIssueCode.invalid_type,
        expected: ZodParsedType.object,
        received: ctx.parsedType
      });
      return INVALID;
    }
    const discriminator = this.discriminator;
    const discriminatorValue = ctx.data[discriminator];
    const option = this.optionsMap.get(discriminatorValue);
    if (!option) {
      addIssueToContext(ctx, {
        code: ZodIssueCode.invalid_union_discriminator,
        options: Array.from(this.optionsMap.keys()),
        path: [discriminator]
      });
      return INVALID;
    }
    if (ctx.common.async) {
      return option._parseAsync({
        data: ctx.data,
        path: ctx.path,
        parent: ctx
      });
    } else {
      return option._parseSync({
        data: ctx.data,
        path: ctx.path,
        parent: ctx
      });
    }
  }
  get discriminator() {
    return this._def.discriminator;
  }
  get options() {
    return this._def.options;
  }
  get optionsMap() {
    return this._def.optionsMap;
  }
  /**
   * The constructor of the discriminated union schema. Its behaviour is very similar to that of the normal z.union() constructor.
   * However, it only allows a union of objects, all of which need to share a discriminator property. This property must
   * have a different value for each object in the union.
   * @param discriminator the name of the discriminator property
   * @param types an array of object schemas
   * @param params
   */
  static create(discriminator, options, params) {
    const optionsMap = /* @__PURE__ */ new Map();
    for (const type2 of options) {
      const discriminatorValues = getDiscriminator(type2.shape[discriminator]);
      if (!discriminatorValues.length) {
        throw new Error(`A discriminator value for key \`${discriminator}\` could not be extracted from all schema options`);
      }
      for (const value of discriminatorValues) {
        if (optionsMap.has(value)) {
          throw new Error(`Discriminator property ${String(discriminator)} has duplicate value ${String(value)}`);
        }
        optionsMap.set(value, type2);
      }
    }
    return new ZodDiscriminatedUnion({
      typeName: ZodFirstPartyTypeKind.ZodDiscriminatedUnion,
      discriminator,
      options,
      optionsMap,
      ...processCreateParams$1(params)
    });
  }
}
function mergeValues(a2, b2) {
  const aType = getParsedType(a2);
  const bType = getParsedType(b2);
  if (a2 === b2) {
    return { valid: true, data: a2 };
  } else if (aType === ZodParsedType.object && bType === ZodParsedType.object) {
    const bKeys = util$1.objectKeys(b2);
    const sharedKeys = util$1.objectKeys(a2).filter((key) => bKeys.indexOf(key) !== -1);
    const newObj = { ...a2, ...b2 };
    for (const key of sharedKeys) {
      const sharedValue = mergeValues(a2[key], b2[key]);
      if (!sharedValue.valid) {
        return { valid: false };
      }
      newObj[key] = sharedValue.data;
    }
    return { valid: true, data: newObj };
  } else if (aType === ZodParsedType.array && bType === ZodParsedType.array) {
    if (a2.length !== b2.length) {
      return { valid: false };
    }
    const newArray = [];
    for (let index = 0; index < a2.length; index++) {
      const itemA = a2[index];
      const itemB = b2[index];
      const sharedValue = mergeValues(itemA, itemB);
      if (!sharedValue.valid) {
        return { valid: false };
      }
      newArray.push(sharedValue.data);
    }
    return { valid: true, data: newArray };
  } else if (aType === ZodParsedType.date && bType === ZodParsedType.date && +a2 === +b2) {
    return { valid: true, data: a2 };
  } else {
    return { valid: false };
  }
}
class ZodIntersection extends ZodType {
  _parse(input) {
    const { status, ctx } = this._processInputParams(input);
    const handleParsed = (parsedLeft, parsedRight) => {
      if (isAborted(parsedLeft) || isAborted(parsedRight)) {
        return INVALID;
      }
      const merged = mergeValues(parsedLeft.value, parsedRight.value);
      if (!merged.valid) {
        addIssueToContext(ctx, {
          code: ZodIssueCode.invalid_intersection_types
        });
        return INVALID;
      }
      if (isDirty(parsedLeft) || isDirty(parsedRight)) {
        status.dirty();
      }
      return { status: status.value, value: merged.data };
    };
    if (ctx.common.async) {
      return Promise.all([
        this._def.left._parseAsync({
          data: ctx.data,
          path: ctx.path,
          parent: ctx
        }),
        this._def.right._parseAsync({
          data: ctx.data,
          path: ctx.path,
          parent: ctx
        })
      ]).then(([left, right]) => handleParsed(left, right));
    } else {
      return handleParsed(this._def.left._parseSync({
        data: ctx.data,
        path: ctx.path,
        parent: ctx
      }), this._def.right._parseSync({
        data: ctx.data,
        path: ctx.path,
        parent: ctx
      }));
    }
  }
}
ZodIntersection.create = (left, right, params) => {
  return new ZodIntersection({
    left,
    right,
    typeName: ZodFirstPartyTypeKind.ZodIntersection,
    ...processCreateParams$1(params)
  });
};
class ZodTuple extends ZodType {
  _parse(input) {
    const { status, ctx } = this._processInputParams(input);
    if (ctx.parsedType !== ZodParsedType.array) {
      addIssueToContext(ctx, {
        code: ZodIssueCode.invalid_type,
        expected: ZodParsedType.array,
        received: ctx.parsedType
      });
      return INVALID;
    }
    if (ctx.data.length < this._def.items.length) {
      addIssueToContext(ctx, {
        code: ZodIssueCode.too_small,
        minimum: this._def.items.length,
        inclusive: true,
        exact: false,
        type: "array"
      });
      return INVALID;
    }
    const rest = this._def.rest;
    if (!rest && ctx.data.length > this._def.items.length) {
      addIssueToContext(ctx, {
        code: ZodIssueCode.too_big,
        maximum: this._def.items.length,
        inclusive: true,
        exact: false,
        type: "array"
      });
      status.dirty();
    }
    const items2 = [...ctx.data].map((item, itemIndex) => {
      const schema = this._def.items[itemIndex] || this._def.rest;
      if (!schema)
        return null;
      return schema._parse(new ParseInputLazyPath(ctx, item, ctx.path, itemIndex));
    }).filter((x2) => !!x2);
    if (ctx.common.async) {
      return Promise.all(items2).then((results) => {
        return ParseStatus.mergeArray(status, results);
      });
    } else {
      return ParseStatus.mergeArray(status, items2);
    }
  }
  get items() {
    return this._def.items;
  }
  rest(rest) {
    return new ZodTuple({
      ...this._def,
      rest
    });
  }
}
ZodTuple.create = (schemas, params) => {
  if (!Array.isArray(schemas)) {
    throw new Error("You must pass an array of schemas to z.tuple([ ... ])");
  }
  return new ZodTuple({
    items: schemas,
    typeName: ZodFirstPartyTypeKind.ZodTuple,
    rest: null,
    ...processCreateParams$1(params)
  });
};
class ZodRecord extends ZodType {
  get keySchema() {
    return this._def.keyType;
  }
  get valueSchema() {
    return this._def.valueType;
  }
  _parse(input) {
    const { status, ctx } = this._processInputParams(input);
    if (ctx.parsedType !== ZodParsedType.object) {
      addIssueToContext(ctx, {
        code: ZodIssueCode.invalid_type,
        expected: ZodParsedType.object,
        received: ctx.parsedType
      });
      return INVALID;
    }
    const pairs = [];
    const keyType = this._def.keyType;
    const valueType = this._def.valueType;
    for (const key in ctx.data) {
      pairs.push({
        key: keyType._parse(new ParseInputLazyPath(ctx, key, ctx.path, key)),
        value: valueType._parse(new ParseInputLazyPath(ctx, ctx.data[key], ctx.path, key)),
        alwaysSet: key in ctx.data
      });
    }
    if (ctx.common.async) {
      return ParseStatus.mergeObjectAsync(status, pairs);
    } else {
      return ParseStatus.mergeObjectSync(status, pairs);
    }
  }
  get element() {
    return this._def.valueType;
  }
  static create(first, second, third) {
    if (second instanceof ZodType) {
      return new ZodRecord({
        keyType: first,
        valueType: second,
        typeName: ZodFirstPartyTypeKind.ZodRecord,
        ...processCreateParams$1(third)
      });
    }
    return new ZodRecord({
      keyType: ZodString.create(),
      valueType: first,
      typeName: ZodFirstPartyTypeKind.ZodRecord,
      ...processCreateParams$1(second)
    });
  }
}
class ZodMap extends ZodType {
  get keySchema() {
    return this._def.keyType;
  }
  get valueSchema() {
    return this._def.valueType;
  }
  _parse(input) {
    const { status, ctx } = this._processInputParams(input);
    if (ctx.parsedType !== ZodParsedType.map) {
      addIssueToContext(ctx, {
        code: ZodIssueCode.invalid_type,
        expected: ZodParsedType.map,
        received: ctx.parsedType
      });
      return INVALID;
    }
    const keyType = this._def.keyType;
    const valueType = this._def.valueType;
    const pairs = [...ctx.data.entries()].map(([key, value], index) => {
      return {
        key: keyType._parse(new ParseInputLazyPath(ctx, key, ctx.path, [index, "key"])),
        value: valueType._parse(new ParseInputLazyPath(ctx, value, ctx.path, [index, "value"]))
      };
    });
    if (ctx.common.async) {
      const finalMap = /* @__PURE__ */ new Map();
      return Promise.resolve().then(async () => {
        for (const pair of pairs) {
          const key = await pair.key;
          const value = await pair.value;
          if (key.status === "aborted" || value.status === "aborted") {
            return INVALID;
          }
          if (key.status === "dirty" || value.status === "dirty") {
            status.dirty();
          }
          finalMap.set(key.value, value.value);
        }
        return { status: status.value, value: finalMap };
      });
    } else {
      const finalMap = /* @__PURE__ */ new Map();
      for (const pair of pairs) {
        const key = pair.key;
        const value = pair.value;
        if (key.status === "aborted" || value.status === "aborted") {
          return INVALID;
        }
        if (key.status === "dirty" || value.status === "dirty") {
          status.dirty();
        }
        finalMap.set(key.value, value.value);
      }
      return { status: status.value, value: finalMap };
    }
  }
}
ZodMap.create = (keyType, valueType, params) => {
  return new ZodMap({
    valueType,
    keyType,
    typeName: ZodFirstPartyTypeKind.ZodMap,
    ...processCreateParams$1(params)
  });
};
class ZodSet extends ZodType {
  _parse(input) {
    const { status, ctx } = this._processInputParams(input);
    if (ctx.parsedType !== ZodParsedType.set) {
      addIssueToContext(ctx, {
        code: ZodIssueCode.invalid_type,
        expected: ZodParsedType.set,
        received: ctx.parsedType
      });
      return INVALID;
    }
    const def = this._def;
    if (def.minSize !== null) {
      if (ctx.data.size < def.minSize.value) {
        addIssueToContext(ctx, {
          code: ZodIssueCode.too_small,
          minimum: def.minSize.value,
          type: "set",
          inclusive: true,
          exact: false,
          message: def.minSize.message
        });
        status.dirty();
      }
    }
    if (def.maxSize !== null) {
      if (ctx.data.size > def.maxSize.value) {
        addIssueToContext(ctx, {
          code: ZodIssueCode.too_big,
          maximum: def.maxSize.value,
          type: "set",
          inclusive: true,
          exact: false,
          message: def.maxSize.message
        });
        status.dirty();
      }
    }
    const valueType = this._def.valueType;
    function finalizeSet(elements2) {
      const parsedSet = /* @__PURE__ */ new Set();
      for (const element of elements2) {
        if (element.status === "aborted")
          return INVALID;
        if (element.status === "dirty")
          status.dirty();
        parsedSet.add(element.value);
      }
      return { status: status.value, value: parsedSet };
    }
    const elements = [...ctx.data.values()].map((item, i2) => valueType._parse(new ParseInputLazyPath(ctx, item, ctx.path, i2)));
    if (ctx.common.async) {
      return Promise.all(elements).then((elements2) => finalizeSet(elements2));
    } else {
      return finalizeSet(elements);
    }
  }
  min(minSize, message) {
    return new ZodSet({
      ...this._def,
      minSize: { value: minSize, message: errorUtil.toString(message) }
    });
  }
  max(maxSize, message) {
    return new ZodSet({
      ...this._def,
      maxSize: { value: maxSize, message: errorUtil.toString(message) }
    });
  }
  size(size, message) {
    return this.min(size, message).max(size, message);
  }
  nonempty(message) {
    return this.min(1, message);
  }
}
ZodSet.create = (valueType, params) => {
  return new ZodSet({
    valueType,
    minSize: null,
    maxSize: null,
    typeName: ZodFirstPartyTypeKind.ZodSet,
    ...processCreateParams$1(params)
  });
};
class ZodLazy extends ZodType {
  get schema() {
    return this._def.getter();
  }
  _parse(input) {
    const { ctx } = this._processInputParams(input);
    const lazySchema = this._def.getter();
    return lazySchema._parse({ data: ctx.data, path: ctx.path, parent: ctx });
  }
}
ZodLazy.create = (getter, params) => {
  return new ZodLazy({
    getter,
    typeName: ZodFirstPartyTypeKind.ZodLazy,
    ...processCreateParams$1(params)
  });
};
class ZodLiteral extends ZodType {
  _parse(input) {
    if (input.data !== this._def.value) {
      const ctx = this._getOrReturnCtx(input);
      addIssueToContext(ctx, {
        received: ctx.data,
        code: ZodIssueCode.invalid_literal,
        expected: this._def.value
      });
      return INVALID;
    }
    return { status: "valid", value: input.data };
  }
  get value() {
    return this._def.value;
  }
}
ZodLiteral.create = (value, params) => {
  return new ZodLiteral({
    value,
    typeName: ZodFirstPartyTypeKind.ZodLiteral,
    ...processCreateParams$1(params)
  });
};
function createZodEnum(values, params) {
  return new ZodEnum({
    values,
    typeName: ZodFirstPartyTypeKind.ZodEnum,
    ...processCreateParams$1(params)
  });
}
class ZodEnum extends ZodType {
  _parse(input) {
    if (typeof input.data !== "string") {
      const ctx = this._getOrReturnCtx(input);
      const expectedValues = this._def.values;
      addIssueToContext(ctx, {
        expected: util$1.joinValues(expectedValues),
        received: ctx.parsedType,
        code: ZodIssueCode.invalid_type
      });
      return INVALID;
    }
    if (!this._cache) {
      this._cache = new Set(this._def.values);
    }
    if (!this._cache.has(input.data)) {
      const ctx = this._getOrReturnCtx(input);
      const expectedValues = this._def.values;
      addIssueToContext(ctx, {
        received: ctx.data,
        code: ZodIssueCode.invalid_enum_value,
        options: expectedValues
      });
      return INVALID;
    }
    return OK(input.data);
  }
  get options() {
    return this._def.values;
  }
  get enum() {
    const enumValues = {};
    for (const val of this._def.values) {
      enumValues[val] = val;
    }
    return enumValues;
  }
  get Values() {
    const enumValues = {};
    for (const val of this._def.values) {
      enumValues[val] = val;
    }
    return enumValues;
  }
  get Enum() {
    const enumValues = {};
    for (const val of this._def.values) {
      enumValues[val] = val;
    }
    return enumValues;
  }
  extract(values, newDef = this._def) {
    return ZodEnum.create(values, {
      ...this._def,
      ...newDef
    });
  }
  exclude(values, newDef = this._def) {
    return ZodEnum.create(this.options.filter((opt) => !values.includes(opt)), {
      ...this._def,
      ...newDef
    });
  }
}
ZodEnum.create = createZodEnum;
class ZodNativeEnum extends ZodType {
  _parse(input) {
    const nativeEnumValues = util$1.getValidEnumValues(this._def.values);
    const ctx = this._getOrReturnCtx(input);
    if (ctx.parsedType !== ZodParsedType.string && ctx.parsedType !== ZodParsedType.number) {
      const expectedValues = util$1.objectValues(nativeEnumValues);
      addIssueToContext(ctx, {
        expected: util$1.joinValues(expectedValues),
        received: ctx.parsedType,
        code: ZodIssueCode.invalid_type
      });
      return INVALID;
    }
    if (!this._cache) {
      this._cache = new Set(util$1.getValidEnumValues(this._def.values));
    }
    if (!this._cache.has(input.data)) {
      const expectedValues = util$1.objectValues(nativeEnumValues);
      addIssueToContext(ctx, {
        received: ctx.data,
        code: ZodIssueCode.invalid_enum_value,
        options: expectedValues
      });
      return INVALID;
    }
    return OK(input.data);
  }
  get enum() {
    return this._def.values;
  }
}
ZodNativeEnum.create = (values, params) => {
  return new ZodNativeEnum({
    values,
    typeName: ZodFirstPartyTypeKind.ZodNativeEnum,
    ...processCreateParams$1(params)
  });
};
class ZodPromise extends ZodType {
  unwrap() {
    return this._def.type;
  }
  _parse(input) {
    const { ctx } = this._processInputParams(input);
    if (ctx.parsedType !== ZodParsedType.promise && ctx.common.async === false) {
      addIssueToContext(ctx, {
        code: ZodIssueCode.invalid_type,
        expected: ZodParsedType.promise,
        received: ctx.parsedType
      });
      return INVALID;
    }
    const promisified = ctx.parsedType === ZodParsedType.promise ? ctx.data : Promise.resolve(ctx.data);
    return OK(promisified.then((data2) => {
      return this._def.type.parseAsync(data2, {
        path: ctx.path,
        errorMap: ctx.common.contextualErrorMap
      });
    }));
  }
}
ZodPromise.create = (schema, params) => {
  return new ZodPromise({
    type: schema,
    typeName: ZodFirstPartyTypeKind.ZodPromise,
    ...processCreateParams$1(params)
  });
};
class ZodEffects extends ZodType {
  innerType() {
    return this._def.schema;
  }
  sourceType() {
    return this._def.schema._def.typeName === ZodFirstPartyTypeKind.ZodEffects ? this._def.schema.sourceType() : this._def.schema;
  }
  _parse(input) {
    const { status, ctx } = this._processInputParams(input);
    const effect = this._def.effect || null;
    const checkCtx = {
      addIssue: (arg) => {
        addIssueToContext(ctx, arg);
        if (arg.fatal) {
          status.abort();
        } else {
          status.dirty();
        }
      },
      get path() {
        return ctx.path;
      }
    };
    checkCtx.addIssue = checkCtx.addIssue.bind(checkCtx);
    if (effect.type === "preprocess") {
      const processed = effect.transform(ctx.data, checkCtx);
      if (ctx.common.async) {
        return Promise.resolve(processed).then(async (processed2) => {
          if (status.value === "aborted")
            return INVALID;
          const result = await this._def.schema._parseAsync({
            data: processed2,
            path: ctx.path,
            parent: ctx
          });
          if (result.status === "aborted")
            return INVALID;
          if (result.status === "dirty")
            return DIRTY(result.value);
          if (status.value === "dirty")
            return DIRTY(result.value);
          return result;
        });
      } else {
        if (status.value === "aborted")
          return INVALID;
        const result = this._def.schema._parseSync({
          data: processed,
          path: ctx.path,
          parent: ctx
        });
        if (result.status === "aborted")
          return INVALID;
        if (result.status === "dirty")
          return DIRTY(result.value);
        if (status.value === "dirty")
          return DIRTY(result.value);
        return result;
      }
    }
    if (effect.type === "refinement") {
      const executeRefinement = (acc) => {
        const result = effect.refinement(acc, checkCtx);
        if (ctx.common.async) {
          return Promise.resolve(result);
        }
        if (result instanceof Promise) {
          throw new Error("Async refinement encountered during synchronous parse operation. Use .parseAsync instead.");
        }
        return acc;
      };
      if (ctx.common.async === false) {
        const inner = this._def.schema._parseSync({
          data: ctx.data,
          path: ctx.path,
          parent: ctx
        });
        if (inner.status === "aborted")
          return INVALID;
        if (inner.status === "dirty")
          status.dirty();
        executeRefinement(inner.value);
        return { status: status.value, value: inner.value };
      } else {
        return this._def.schema._parseAsync({ data: ctx.data, path: ctx.path, parent: ctx }).then((inner) => {
          if (inner.status === "aborted")
            return INVALID;
          if (inner.status === "dirty")
            status.dirty();
          return executeRefinement(inner.value).then(() => {
            return { status: status.value, value: inner.value };
          });
        });
      }
    }
    if (effect.type === "transform") {
      if (ctx.common.async === false) {
        const base = this._def.schema._parseSync({
          data: ctx.data,
          path: ctx.path,
          parent: ctx
        });
        if (!isValid(base))
          return INVALID;
        const result = effect.transform(base.value, checkCtx);
        if (result instanceof Promise) {
          throw new Error(`Asynchronous transform encountered during synchronous parse operation. Use .parseAsync instead.`);
        }
        return { status: status.value, value: result };
      } else {
        return this._def.schema._parseAsync({ data: ctx.data, path: ctx.path, parent: ctx }).then((base) => {
          if (!isValid(base))
            return INVALID;
          return Promise.resolve(effect.transform(base.value, checkCtx)).then((result) => ({
            status: status.value,
            value: result
          }));
        });
      }
    }
    util$1.assertNever(effect);
  }
}
ZodEffects.create = (schema, effect, params) => {
  return new ZodEffects({
    schema,
    typeName: ZodFirstPartyTypeKind.ZodEffects,
    effect,
    ...processCreateParams$1(params)
  });
};
ZodEffects.createWithPreprocess = (preprocess, schema, params) => {
  return new ZodEffects({
    schema,
    effect: { type: "preprocess", transform: preprocess },
    typeName: ZodFirstPartyTypeKind.ZodEffects,
    ...processCreateParams$1(params)
  });
};
class ZodOptional extends ZodType {
  _parse(input) {
    const parsedType = this._getType(input);
    if (parsedType === ZodParsedType.undefined) {
      return OK(void 0);
    }
    return this._def.innerType._parse(input);
  }
  unwrap() {
    return this._def.innerType;
  }
}
ZodOptional.create = (type2, params) => {
  return new ZodOptional({
    innerType: type2,
    typeName: ZodFirstPartyTypeKind.ZodOptional,
    ...processCreateParams$1(params)
  });
};
class ZodNullable extends ZodType {
  _parse(input) {
    const parsedType = this._getType(input);
    if (parsedType === ZodParsedType.null) {
      return OK(null);
    }
    return this._def.innerType._parse(input);
  }
  unwrap() {
    return this._def.innerType;
  }
}
ZodNullable.create = (type2, params) => {
  return new ZodNullable({
    innerType: type2,
    typeName: ZodFirstPartyTypeKind.ZodNullable,
    ...processCreateParams$1(params)
  });
};
class ZodDefault extends ZodType {
  _parse(input) {
    const { ctx } = this._processInputParams(input);
    let data2 = ctx.data;
    if (ctx.parsedType === ZodParsedType.undefined) {
      data2 = this._def.defaultValue();
    }
    return this._def.innerType._parse({
      data: data2,
      path: ctx.path,
      parent: ctx
    });
  }
  removeDefault() {
    return this._def.innerType;
  }
}
ZodDefault.create = (type2, params) => {
  return new ZodDefault({
    innerType: type2,
    typeName: ZodFirstPartyTypeKind.ZodDefault,
    defaultValue: typeof params.default === "function" ? params.default : () => params.default,
    ...processCreateParams$1(params)
  });
};
class ZodCatch extends ZodType {
  _parse(input) {
    const { ctx } = this._processInputParams(input);
    const newCtx = {
      ...ctx,
      common: {
        ...ctx.common,
        issues: []
      }
    };
    const result = this._def.innerType._parse({
      data: newCtx.data,
      path: newCtx.path,
      parent: {
        ...newCtx
      }
    });
    if (isAsync(result)) {
      return result.then((result2) => {
        return {
          status: "valid",
          value: result2.status === "valid" ? result2.value : this._def.catchValue({
            get error() {
              return new ZodError(newCtx.common.issues);
            },
            input: newCtx.data
          })
        };
      });
    } else {
      return {
        status: "valid",
        value: result.status === "valid" ? result.value : this._def.catchValue({
          get error() {
            return new ZodError(newCtx.common.issues);
          },
          input: newCtx.data
        })
      };
    }
  }
  removeCatch() {
    return this._def.innerType;
  }
}
ZodCatch.create = (type2, params) => {
  return new ZodCatch({
    innerType: type2,
    typeName: ZodFirstPartyTypeKind.ZodCatch,
    catchValue: typeof params.catch === "function" ? params.catch : () => params.catch,
    ...processCreateParams$1(params)
  });
};
class ZodNaN extends ZodType {
  _parse(input) {
    const parsedType = this._getType(input);
    if (parsedType !== ZodParsedType.nan) {
      const ctx = this._getOrReturnCtx(input);
      addIssueToContext(ctx, {
        code: ZodIssueCode.invalid_type,
        expected: ZodParsedType.nan,
        received: ctx.parsedType
      });
      return INVALID;
    }
    return { status: "valid", value: input.data };
  }
}
ZodNaN.create = (params) => {
  return new ZodNaN({
    typeName: ZodFirstPartyTypeKind.ZodNaN,
    ...processCreateParams$1(params)
  });
};
class ZodBranded extends ZodType {
  _parse(input) {
    const { ctx } = this._processInputParams(input);
    const data2 = ctx.data;
    return this._def.type._parse({
      data: data2,
      path: ctx.path,
      parent: ctx
    });
  }
  unwrap() {
    return this._def.type;
  }
}
class ZodPipeline extends ZodType {
  _parse(input) {
    const { status, ctx } = this._processInputParams(input);
    if (ctx.common.async) {
      const handleAsync = async () => {
        const inResult = await this._def.in._parseAsync({
          data: ctx.data,
          path: ctx.path,
          parent: ctx
        });
        if (inResult.status === "aborted")
          return INVALID;
        if (inResult.status === "dirty") {
          status.dirty();
          return DIRTY(inResult.value);
        } else {
          return this._def.out._parseAsync({
            data: inResult.value,
            path: ctx.path,
            parent: ctx
          });
        }
      };
      return handleAsync();
    } else {
      const inResult = this._def.in._parseSync({
        data: ctx.data,
        path: ctx.path,
        parent: ctx
      });
      if (inResult.status === "aborted")
        return INVALID;
      if (inResult.status === "dirty") {
        status.dirty();
        return {
          status: "dirty",
          value: inResult.value
        };
      } else {
        return this._def.out._parseSync({
          data: inResult.value,
          path: ctx.path,
          parent: ctx
        });
      }
    }
  }
  static create(a2, b2) {
    return new ZodPipeline({
      in: a2,
      out: b2,
      typeName: ZodFirstPartyTypeKind.ZodPipeline
    });
  }
}
class ZodReadonly extends ZodType {
  _parse(input) {
    const result = this._def.innerType._parse(input);
    const freeze = (data2) => {
      if (isValid(data2)) {
        data2.value = Object.freeze(data2.value);
      }
      return data2;
    };
    return isAsync(result) ? result.then((data2) => freeze(data2)) : freeze(result);
  }
  unwrap() {
    return this._def.innerType;
  }
}
ZodReadonly.create = (type2, params) => {
  return new ZodReadonly({
    innerType: type2,
    typeName: ZodFirstPartyTypeKind.ZodReadonly,
    ...processCreateParams$1(params)
  });
};
var ZodFirstPartyTypeKind;
(function(ZodFirstPartyTypeKind2) {
  ZodFirstPartyTypeKind2["ZodString"] = "ZodString";
  ZodFirstPartyTypeKind2["ZodNumber"] = "ZodNumber";
  ZodFirstPartyTypeKind2["ZodNaN"] = "ZodNaN";
  ZodFirstPartyTypeKind2["ZodBigInt"] = "ZodBigInt";
  ZodFirstPartyTypeKind2["ZodBoolean"] = "ZodBoolean";
  ZodFirstPartyTypeKind2["ZodDate"] = "ZodDate";
  ZodFirstPartyTypeKind2["ZodSymbol"] = "ZodSymbol";
  ZodFirstPartyTypeKind2["ZodUndefined"] = "ZodUndefined";
  ZodFirstPartyTypeKind2["ZodNull"] = "ZodNull";
  ZodFirstPartyTypeKind2["ZodAny"] = "ZodAny";
  ZodFirstPartyTypeKind2["ZodUnknown"] = "ZodUnknown";
  ZodFirstPartyTypeKind2["ZodNever"] = "ZodNever";
  ZodFirstPartyTypeKind2["ZodVoid"] = "ZodVoid";
  ZodFirstPartyTypeKind2["ZodArray"] = "ZodArray";
  ZodFirstPartyTypeKind2["ZodObject"] = "ZodObject";
  ZodFirstPartyTypeKind2["ZodUnion"] = "ZodUnion";
  ZodFirstPartyTypeKind2["ZodDiscriminatedUnion"] = "ZodDiscriminatedUnion";
  ZodFirstPartyTypeKind2["ZodIntersection"] = "ZodIntersection";
  ZodFirstPartyTypeKind2["ZodTuple"] = "ZodTuple";
  ZodFirstPartyTypeKind2["ZodRecord"] = "ZodRecord";
  ZodFirstPartyTypeKind2["ZodMap"] = "ZodMap";
  ZodFirstPartyTypeKind2["ZodSet"] = "ZodSet";
  ZodFirstPartyTypeKind2["ZodFunction"] = "ZodFunction";
  ZodFirstPartyTypeKind2["ZodLazy"] = "ZodLazy";
  ZodFirstPartyTypeKind2["ZodLiteral"] = "ZodLiteral";
  ZodFirstPartyTypeKind2["ZodEnum"] = "ZodEnum";
  ZodFirstPartyTypeKind2["ZodEffects"] = "ZodEffects";
  ZodFirstPartyTypeKind2["ZodNativeEnum"] = "ZodNativeEnum";
  ZodFirstPartyTypeKind2["ZodOptional"] = "ZodOptional";
  ZodFirstPartyTypeKind2["ZodNullable"] = "ZodNullable";
  ZodFirstPartyTypeKind2["ZodDefault"] = "ZodDefault";
  ZodFirstPartyTypeKind2["ZodCatch"] = "ZodCatch";
  ZodFirstPartyTypeKind2["ZodPromise"] = "ZodPromise";
  ZodFirstPartyTypeKind2["ZodBranded"] = "ZodBranded";
  ZodFirstPartyTypeKind2["ZodPipeline"] = "ZodPipeline";
  ZodFirstPartyTypeKind2["ZodReadonly"] = "ZodReadonly";
})(ZodFirstPartyTypeKind || (ZodFirstPartyTypeKind = {}));
const stringType = ZodString.create;
const numberType = ZodNumber.create;
const booleanType = ZodBoolean.create;
const anyType = ZodAny.create;
const unknownType = ZodUnknown.create;
ZodNever.create;
const arrayType = ZodArray.create;
const objectType = ZodObject.create;
const unionType = ZodUnion.create;
const discriminatedUnionType = ZodDiscriminatedUnion.create;
ZodIntersection.create;
ZodTuple.create;
const recordType = ZodRecord.create;
const literalType = ZodLiteral.create;
const enumType = ZodEnum.create;
ZodPromise.create;
const optionalType = ZodOptional.create;
ZodNullable.create;
const LATEST_PROTOCOL_VERSION = "2025-06-18";
const SUPPORTED_PROTOCOL_VERSIONS = [LATEST_PROTOCOL_VERSION, "2025-03-26", "2024-11-05", "2024-10-07"];
const JSONRPC_VERSION = "2.0";
const ProgressTokenSchema = unionType([stringType(), numberType().int()]);
const CursorSchema = stringType();
const RequestMetaSchema = objectType({
  /**
   * If specified, the caller is requesting out-of-band progress notifications for this request (as represented by notifications/progress). The value of this parameter is an opaque token that will be attached to any subsequent notifications. The receiver is not obligated to provide these notifications.
   */
  progressToken: optionalType(ProgressTokenSchema)
}).passthrough();
const BaseRequestParamsSchema = objectType({
  _meta: optionalType(RequestMetaSchema)
}).passthrough();
const RequestSchema = objectType({
  method: stringType(),
  params: optionalType(BaseRequestParamsSchema)
});
const BaseNotificationParamsSchema = objectType({
  /**
   * See [MCP specification](https://github.com/modelcontextprotocol/modelcontextprotocol/blob/47339c03c143bb4ec01a26e721a1b8fe66634ebe/docs/specification/draft/basic/index.mdx#general-fields)
   * for notes on _meta usage.
   */
  _meta: optionalType(objectType({}).passthrough())
}).passthrough();
const NotificationSchema = objectType({
  method: stringType(),
  params: optionalType(BaseNotificationParamsSchema)
});
const ResultSchema = objectType({
  /**
   * See [MCP specification](https://github.com/modelcontextprotocol/modelcontextprotocol/blob/47339c03c143bb4ec01a26e721a1b8fe66634ebe/docs/specification/draft/basic/index.mdx#general-fields)
   * for notes on _meta usage.
   */
  _meta: optionalType(objectType({}).passthrough())
}).passthrough();
const RequestIdSchema = unionType([stringType(), numberType().int()]);
const JSONRPCRequestSchema = objectType({
  jsonrpc: literalType(JSONRPC_VERSION),
  id: RequestIdSchema
}).merge(RequestSchema).strict();
const isJSONRPCRequest = (value) => JSONRPCRequestSchema.safeParse(value).success;
const JSONRPCNotificationSchema = objectType({
  jsonrpc: literalType(JSONRPC_VERSION)
}).merge(NotificationSchema).strict();
const isJSONRPCNotification = (value) => JSONRPCNotificationSchema.safeParse(value).success;
const JSONRPCResponseSchema = objectType({
  jsonrpc: literalType(JSONRPC_VERSION),
  id: RequestIdSchema,
  result: ResultSchema
}).strict();
const isJSONRPCResponse = (value) => JSONRPCResponseSchema.safeParse(value).success;
var ErrorCode;
(function(ErrorCode2) {
  ErrorCode2[ErrorCode2["ConnectionClosed"] = -32e3] = "ConnectionClosed";
  ErrorCode2[ErrorCode2["RequestTimeout"] = -32001] = "RequestTimeout";
  ErrorCode2[ErrorCode2["ParseError"] = -32700] = "ParseError";
  ErrorCode2[ErrorCode2["InvalidRequest"] = -32600] = "InvalidRequest";
  ErrorCode2[ErrorCode2["MethodNotFound"] = -32601] = "MethodNotFound";
  ErrorCode2[ErrorCode2["InvalidParams"] = -32602] = "InvalidParams";
  ErrorCode2[ErrorCode2["InternalError"] = -32603] = "InternalError";
})(ErrorCode || (ErrorCode = {}));
const JSONRPCErrorSchema = objectType({
  jsonrpc: literalType(JSONRPC_VERSION),
  id: RequestIdSchema,
  error: objectType({
    /**
     * The error type that occurred.
     */
    code: numberType().int(),
    /**
     * A short description of the error. The message SHOULD be limited to a concise single sentence.
     */
    message: stringType(),
    /**
     * Additional information about the error. The value of this member is defined by the sender (e.g. detailed error information, nested errors etc.).
     */
    data: optionalType(unknownType())
  })
}).strict();
const isJSONRPCError = (value) => JSONRPCErrorSchema.safeParse(value).success;
unionType([JSONRPCRequestSchema, JSONRPCNotificationSchema, JSONRPCResponseSchema, JSONRPCErrorSchema]);
const EmptyResultSchema = ResultSchema.strict();
const CancelledNotificationSchema = NotificationSchema.extend({
  method: literalType("notifications/cancelled"),
  params: BaseNotificationParamsSchema.extend({
    /**
     * The ID of the request to cancel.
     *
     * This MUST correspond to the ID of a request previously issued in the same direction.
     */
    requestId: RequestIdSchema,
    /**
     * An optional string describing the reason for the cancellation. This MAY be logged or presented to the user.
     */
    reason: stringType().optional()
  })
});
const IconSchema = objectType({
  /**
   * URL or data URI for the icon.
   */
  src: stringType(),
  /**
   * Optional MIME type for the icon.
   */
  mimeType: optionalType(stringType()),
  /**
   * Optional array of strings that specify sizes at which the icon can be used.
   * Each string should be in WxH format (e.g., `"48x48"`, `"96x96"`) or `"any"` for scalable formats like SVG.
   *
   * If not provided, the client should assume that the icon can be used at any size.
   */
  sizes: optionalType(arrayType(stringType()))
}).passthrough();
const IconsSchema = objectType({
  /**
   * Optional set of sized icons that the client can display in a user interface.
   *
   * Clients that support rendering icons MUST support at least the following MIME types:
   * - `image/png` - PNG images (safe, universal compatibility)
   * - `image/jpeg` (and `image/jpg`) - JPEG images (safe, universal compatibility)
   *
   * Clients that support rendering icons SHOULD also support:
   * - `image/svg+xml` - SVG images (scalable but requires security precautions)
   * - `image/webp` - WebP images (modern, efficient format)
   */
  icons: arrayType(IconSchema).optional()
}).passthrough();
const BaseMetadataSchema = objectType({
  /** Intended for programmatic or logical use, but used as a display name in past specs or fallback */
  name: stringType(),
  /**
   * Intended for UI and end-user contexts — optimized to be human-readable and easily understood,
   * even by those unfamiliar with domain-specific terminology.
   *
   * If not provided, the name should be used for display (except for Tool,
   * where `annotations.title` should be given precedence over using `name`,
   * if present).
   */
  title: optionalType(stringType())
}).passthrough();
const ImplementationSchema = BaseMetadataSchema.extend({
  version: stringType(),
  /**
   * An optional URL of the website for this implementation.
   */
  websiteUrl: optionalType(stringType())
}).merge(IconsSchema);
const ClientCapabilitiesSchema = objectType({
  /**
   * Experimental, non-standard capabilities that the client supports.
   */
  experimental: optionalType(objectType({}).passthrough()),
  /**
   * Present if the client supports sampling from an LLM.
   */
  sampling: optionalType(objectType({}).passthrough()),
  /**
   * Present if the client supports eliciting user input.
   */
  elicitation: optionalType(objectType({}).passthrough()),
  /**
   * Present if the client supports listing roots.
   */
  roots: optionalType(objectType({
    /**
     * Whether the client supports issuing notifications for changes to the roots list.
     */
    listChanged: optionalType(booleanType())
  }).passthrough())
}).passthrough();
const InitializeRequestSchema = RequestSchema.extend({
  method: literalType("initialize"),
  params: BaseRequestParamsSchema.extend({
    /**
     * The latest version of the Model Context Protocol that the client supports. The client MAY decide to support older versions as well.
     */
    protocolVersion: stringType(),
    capabilities: ClientCapabilitiesSchema,
    clientInfo: ImplementationSchema
  })
});
const ServerCapabilitiesSchema = objectType({
  /**
   * Experimental, non-standard capabilities that the server supports.
   */
  experimental: optionalType(objectType({}).passthrough()),
  /**
   * Present if the server supports sending log messages to the client.
   */
  logging: optionalType(objectType({}).passthrough()),
  /**
   * Present if the server supports sending completions to the client.
   */
  completions: optionalType(objectType({}).passthrough()),
  /**
   * Present if the server offers any prompt templates.
   */
  prompts: optionalType(objectType({
    /**
     * Whether this server supports issuing notifications for changes to the prompt list.
     */
    listChanged: optionalType(booleanType())
  }).passthrough()),
  /**
   * Present if the server offers any resources to read.
   */
  resources: optionalType(objectType({
    /**
     * Whether this server supports clients subscribing to resource updates.
     */
    subscribe: optionalType(booleanType()),
    /**
     * Whether this server supports issuing notifications for changes to the resource list.
     */
    listChanged: optionalType(booleanType())
  }).passthrough()),
  /**
   * Present if the server offers any tools to call.
   */
  tools: optionalType(objectType({
    /**
     * Whether this server supports issuing notifications for changes to the tool list.
     */
    listChanged: optionalType(booleanType())
  }).passthrough())
}).passthrough();
const InitializeResultSchema = ResultSchema.extend({
  /**
   * The version of the Model Context Protocol that the server wants to use. This may not match the version that the client requested. If the client cannot support this version, it MUST disconnect.
   */
  protocolVersion: stringType(),
  capabilities: ServerCapabilitiesSchema,
  serverInfo: ImplementationSchema,
  /**
   * Instructions describing how to use the server and its features.
   *
   * This can be used by clients to improve the LLM's understanding of available tools, resources, etc. It can be thought of like a "hint" to the model. For example, this information MAY be added to the system prompt.
   */
  instructions: optionalType(stringType())
});
const InitializedNotificationSchema = NotificationSchema.extend({
  method: literalType("notifications/initialized")
});
const PingRequestSchema = RequestSchema.extend({
  method: literalType("ping")
});
const ProgressSchema = objectType({
  /**
   * The progress thus far. This should increase every time progress is made, even if the total is unknown.
   */
  progress: numberType(),
  /**
   * Total number of items to process (or total progress required), if known.
   */
  total: optionalType(numberType()),
  /**
   * An optional message describing the current progress.
   */
  message: optionalType(stringType())
}).passthrough();
const ProgressNotificationSchema = NotificationSchema.extend({
  method: literalType("notifications/progress"),
  params: BaseNotificationParamsSchema.merge(ProgressSchema).extend({
    /**
     * The progress token which was given in the initial request, used to associate this notification with the request that is proceeding.
     */
    progressToken: ProgressTokenSchema
  })
});
const PaginatedRequestSchema = RequestSchema.extend({
  params: BaseRequestParamsSchema.extend({
    /**
     * An opaque token representing the current pagination position.
     * If provided, the server should return results starting after this cursor.
     */
    cursor: optionalType(CursorSchema)
  }).optional()
});
const PaginatedResultSchema = ResultSchema.extend({
  /**
   * An opaque token representing the pagination position after the last returned result.
   * If present, there may be more results available.
   */
  nextCursor: optionalType(CursorSchema)
});
const ResourceContentsSchema = objectType({
  /**
   * The URI of this resource.
   */
  uri: stringType(),
  /**
   * The MIME type of this resource, if known.
   */
  mimeType: optionalType(stringType()),
  /**
   * See [MCP specification](https://github.com/modelcontextprotocol/modelcontextprotocol/blob/47339c03c143bb4ec01a26e721a1b8fe66634ebe/docs/specification/draft/basic/index.mdx#general-fields)
   * for notes on _meta usage.
   */
  _meta: optionalType(objectType({}).passthrough())
}).passthrough();
const TextResourceContentsSchema = ResourceContentsSchema.extend({
  /**
   * The text of the item. This must only be set if the item can actually be represented as text (not binary data).
   */
  text: stringType()
});
const Base64Schema = stringType().refine((val) => {
  try {
    atob(val);
    return true;
  } catch (_a) {
    return false;
  }
}, { message: "Invalid Base64 string" });
const BlobResourceContentsSchema = ResourceContentsSchema.extend({
  /**
   * A base64-encoded string representing the binary data of the item.
   */
  blob: Base64Schema
});
const ResourceSchema = BaseMetadataSchema.extend({
  /**
   * The URI of this resource.
   */
  uri: stringType(),
  /**
   * A description of what this resource represents.
   *
   * This can be used by clients to improve the LLM's understanding of available resources. It can be thought of like a "hint" to the model.
   */
  description: optionalType(stringType()),
  /**
   * The MIME type of this resource, if known.
   */
  mimeType: optionalType(stringType()),
  /**
   * See [MCP specification](https://github.com/modelcontextprotocol/modelcontextprotocol/blob/47339c03c143bb4ec01a26e721a1b8fe66634ebe/docs/specification/draft/basic/index.mdx#general-fields)
   * for notes on _meta usage.
   */
  _meta: optionalType(objectType({}).passthrough())
}).merge(IconsSchema);
const ResourceTemplateSchema = BaseMetadataSchema.extend({
  /**
   * A URI template (according to RFC 6570) that can be used to construct resource URIs.
   */
  uriTemplate: stringType(),
  /**
   * A description of what this template is for.
   *
   * This can be used by clients to improve the LLM's understanding of available resources. It can be thought of like a "hint" to the model.
   */
  description: optionalType(stringType()),
  /**
   * The MIME type for all resources that match this template. This should only be included if all resources matching this template have the same type.
   */
  mimeType: optionalType(stringType()),
  /**
   * See [MCP specification](https://github.com/modelcontextprotocol/modelcontextprotocol/blob/47339c03c143bb4ec01a26e721a1b8fe66634ebe/docs/specification/draft/basic/index.mdx#general-fields)
   * for notes on _meta usage.
   */
  _meta: optionalType(objectType({}).passthrough())
}).merge(IconsSchema);
const ListResourcesRequestSchema = PaginatedRequestSchema.extend({
  method: literalType("resources/list")
});
const ListResourcesResultSchema = PaginatedResultSchema.extend({
  resources: arrayType(ResourceSchema)
});
const ListResourceTemplatesRequestSchema = PaginatedRequestSchema.extend({
  method: literalType("resources/templates/list")
});
const ListResourceTemplatesResultSchema = PaginatedResultSchema.extend({
  resourceTemplates: arrayType(ResourceTemplateSchema)
});
const ReadResourceRequestSchema = RequestSchema.extend({
  method: literalType("resources/read"),
  params: BaseRequestParamsSchema.extend({
    /**
     * The URI of the resource to read. The URI can use any protocol; it is up to the server how to interpret it.
     */
    uri: stringType()
  })
});
const ReadResourceResultSchema = ResultSchema.extend({
  contents: arrayType(unionType([TextResourceContentsSchema, BlobResourceContentsSchema]))
});
const ResourceListChangedNotificationSchema = NotificationSchema.extend({
  method: literalType("notifications/resources/list_changed")
});
const SubscribeRequestSchema = RequestSchema.extend({
  method: literalType("resources/subscribe"),
  params: BaseRequestParamsSchema.extend({
    /**
     * The URI of the resource to subscribe to. The URI can use any protocol; it is up to the server how to interpret it.
     */
    uri: stringType()
  })
});
const UnsubscribeRequestSchema = RequestSchema.extend({
  method: literalType("resources/unsubscribe"),
  params: BaseRequestParamsSchema.extend({
    /**
     * The URI of the resource to unsubscribe from.
     */
    uri: stringType()
  })
});
const ResourceUpdatedNotificationSchema = NotificationSchema.extend({
  method: literalType("notifications/resources/updated"),
  params: BaseNotificationParamsSchema.extend({
    /**
     * The URI of the resource that has been updated. This might be a sub-resource of the one that the client actually subscribed to.
     */
    uri: stringType()
  })
});
const PromptArgumentSchema = objectType({
  /**
   * The name of the argument.
   */
  name: stringType(),
  /**
   * A human-readable description of the argument.
   */
  description: optionalType(stringType()),
  /**
   * Whether this argument must be provided.
   */
  required: optionalType(booleanType())
}).passthrough();
const PromptSchema = BaseMetadataSchema.extend({
  /**
   * An optional description of what this prompt provides
   */
  description: optionalType(stringType()),
  /**
   * A list of arguments to use for templating the prompt.
   */
  arguments: optionalType(arrayType(PromptArgumentSchema)),
  /**
   * See [MCP specification](https://github.com/modelcontextprotocol/modelcontextprotocol/blob/47339c03c143bb4ec01a26e721a1b8fe66634ebe/docs/specification/draft/basic/index.mdx#general-fields)
   * for notes on _meta usage.
   */
  _meta: optionalType(objectType({}).passthrough())
}).merge(IconsSchema);
const ListPromptsRequestSchema = PaginatedRequestSchema.extend({
  method: literalType("prompts/list")
});
const ListPromptsResultSchema = PaginatedResultSchema.extend({
  prompts: arrayType(PromptSchema)
});
const GetPromptRequestSchema = RequestSchema.extend({
  method: literalType("prompts/get"),
  params: BaseRequestParamsSchema.extend({
    /**
     * The name of the prompt or prompt template.
     */
    name: stringType(),
    /**
     * Arguments to use for templating the prompt.
     */
    arguments: optionalType(recordType(stringType()))
  })
});
const TextContentSchema = objectType({
  type: literalType("text"),
  /**
   * The text content of the message.
   */
  text: stringType(),
  /**
   * See [MCP specification](https://github.com/modelcontextprotocol/modelcontextprotocol/blob/47339c03c143bb4ec01a26e721a1b8fe66634ebe/docs/specification/draft/basic/index.mdx#general-fields)
   * for notes on _meta usage.
   */
  _meta: optionalType(objectType({}).passthrough())
}).passthrough();
const ImageContentSchema = objectType({
  type: literalType("image"),
  /**
   * The base64-encoded image data.
   */
  data: Base64Schema,
  /**
   * The MIME type of the image. Different providers may support different image types.
   */
  mimeType: stringType(),
  /**
   * See [MCP specification](https://github.com/modelcontextprotocol/modelcontextprotocol/blob/47339c03c143bb4ec01a26e721a1b8fe66634ebe/docs/specification/draft/basic/index.mdx#general-fields)
   * for notes on _meta usage.
   */
  _meta: optionalType(objectType({}).passthrough())
}).passthrough();
const AudioContentSchema = objectType({
  type: literalType("audio"),
  /**
   * The base64-encoded audio data.
   */
  data: Base64Schema,
  /**
   * The MIME type of the audio. Different providers may support different audio types.
   */
  mimeType: stringType(),
  /**
   * See [MCP specification](https://github.com/modelcontextprotocol/modelcontextprotocol/blob/47339c03c143bb4ec01a26e721a1b8fe66634ebe/docs/specification/draft/basic/index.mdx#general-fields)
   * for notes on _meta usage.
   */
  _meta: optionalType(objectType({}).passthrough())
}).passthrough();
const EmbeddedResourceSchema = objectType({
  type: literalType("resource"),
  resource: unionType([TextResourceContentsSchema, BlobResourceContentsSchema]),
  /**
   * See [MCP specification](https://github.com/modelcontextprotocol/modelcontextprotocol/blob/47339c03c143bb4ec01a26e721a1b8fe66634ebe/docs/specification/draft/basic/index.mdx#general-fields)
   * for notes on _meta usage.
   */
  _meta: optionalType(objectType({}).passthrough())
}).passthrough();
const ResourceLinkSchema = ResourceSchema.extend({
  type: literalType("resource_link")
});
const ContentBlockSchema = unionType([
  TextContentSchema,
  ImageContentSchema,
  AudioContentSchema,
  ResourceLinkSchema,
  EmbeddedResourceSchema
]);
const PromptMessageSchema = objectType({
  role: enumType(["user", "assistant"]),
  content: ContentBlockSchema
}).passthrough();
const GetPromptResultSchema = ResultSchema.extend({
  /**
   * An optional description for the prompt.
   */
  description: optionalType(stringType()),
  messages: arrayType(PromptMessageSchema)
});
const PromptListChangedNotificationSchema = NotificationSchema.extend({
  method: literalType("notifications/prompts/list_changed")
});
const ToolAnnotationsSchema = objectType({
  /**
   * A human-readable title for the tool.
   */
  title: optionalType(stringType()),
  /**
   * If true, the tool does not modify its environment.
   *
   * Default: false
   */
  readOnlyHint: optionalType(booleanType()),
  /**
   * If true, the tool may perform destructive updates to its environment.
   * If false, the tool performs only additive updates.
   *
   * (This property is meaningful only when `readOnlyHint == false`)
   *
   * Default: true
   */
  destructiveHint: optionalType(booleanType()),
  /**
   * If true, calling the tool repeatedly with the same arguments
   * will have no additional effect on the its environment.
   *
   * (This property is meaningful only when `readOnlyHint == false`)
   *
   * Default: false
   */
  idempotentHint: optionalType(booleanType()),
  /**
   * If true, this tool may interact with an "open world" of external
   * entities. If false, the tool's domain of interaction is closed.
   * For example, the world of a web search tool is open, whereas that
   * of a memory tool is not.
   *
   * Default: true
   */
  openWorldHint: optionalType(booleanType())
}).passthrough();
const ToolSchema = BaseMetadataSchema.extend({
  /**
   * A human-readable description of the tool.
   */
  description: optionalType(stringType()),
  /**
   * A JSON Schema object defining the expected parameters for the tool.
   */
  inputSchema: objectType({
    type: literalType("object"),
    properties: optionalType(objectType({}).passthrough()),
    required: optionalType(arrayType(stringType()))
  }).passthrough(),
  /**
   * An optional JSON Schema object defining the structure of the tool's output returned in
   * the structuredContent field of a CallToolResult.
   */
  outputSchema: optionalType(objectType({
    type: literalType("object"),
    properties: optionalType(objectType({}).passthrough()),
    required: optionalType(arrayType(stringType()))
  }).passthrough()),
  /**
   * Optional additional tool information.
   */
  annotations: optionalType(ToolAnnotationsSchema),
  /**
   * See [MCP specification](https://github.com/modelcontextprotocol/modelcontextprotocol/blob/47339c03c143bb4ec01a26e721a1b8fe66634ebe/docs/specification/draft/basic/index.mdx#general-fields)
   * for notes on _meta usage.
   */
  _meta: optionalType(objectType({}).passthrough())
}).merge(IconsSchema);
const ListToolsRequestSchema = PaginatedRequestSchema.extend({
  method: literalType("tools/list")
});
const ListToolsResultSchema = PaginatedResultSchema.extend({
  tools: arrayType(ToolSchema)
});
const CallToolResultSchema = ResultSchema.extend({
  /**
   * A list of content objects that represent the result of the tool call.
   *
   * If the Tool does not define an outputSchema, this field MUST be present in the result.
   * For backwards compatibility, this field is always present, but it may be empty.
   */
  content: arrayType(ContentBlockSchema).default([]),
  /**
   * An object containing structured tool output.
   *
   * If the Tool defines an outputSchema, this field MUST be present in the result, and contain a JSON object that matches the schema.
   */
  structuredContent: objectType({}).passthrough().optional(),
  /**
   * Whether the tool call ended in an error.
   *
   * If not set, this is assumed to be false (the call was successful).
   *
   * Any errors that originate from the tool SHOULD be reported inside the result
   * object, with `isError` set to true, _not_ as an MCP protocol-level error
   * response. Otherwise, the LLM would not be able to see that an error occurred
   * and self-correct.
   *
   * However, any errors in _finding_ the tool, an error indicating that the
   * server does not support tool calls, or any other exceptional conditions,
   * should be reported as an MCP error response.
   */
  isError: optionalType(booleanType())
});
CallToolResultSchema.or(ResultSchema.extend({
  toolResult: unknownType()
}));
const CallToolRequestSchema = RequestSchema.extend({
  method: literalType("tools/call"),
  params: BaseRequestParamsSchema.extend({
    name: stringType(),
    arguments: optionalType(recordType(unknownType()))
  })
});
const ToolListChangedNotificationSchema = NotificationSchema.extend({
  method: literalType("notifications/tools/list_changed")
});
const LoggingLevelSchema = enumType(["debug", "info", "notice", "warning", "error", "critical", "alert", "emergency"]);
const SetLevelRequestSchema = RequestSchema.extend({
  method: literalType("logging/setLevel"),
  params: BaseRequestParamsSchema.extend({
    /**
     * The level of logging that the client wants to receive from the server. The server should send all logs at this level and higher (i.e., more severe) to the client as notifications/logging/message.
     */
    level: LoggingLevelSchema
  })
});
const LoggingMessageNotificationSchema = NotificationSchema.extend({
  method: literalType("notifications/message"),
  params: BaseNotificationParamsSchema.extend({
    /**
     * The severity of this log message.
     */
    level: LoggingLevelSchema,
    /**
     * An optional name of the logger issuing this message.
     */
    logger: optionalType(stringType()),
    /**
     * The data to be logged, such as a string message or an object. Any JSON serializable type is allowed here.
     */
    data: unknownType()
  })
});
const ModelHintSchema = objectType({
  /**
   * A hint for a model name.
   */
  name: stringType().optional()
}).passthrough();
const ModelPreferencesSchema = objectType({
  /**
   * Optional hints to use for model selection.
   */
  hints: optionalType(arrayType(ModelHintSchema)),
  /**
   * How much to prioritize cost when selecting a model.
   */
  costPriority: optionalType(numberType().min(0).max(1)),
  /**
   * How much to prioritize sampling speed (latency) when selecting a model.
   */
  speedPriority: optionalType(numberType().min(0).max(1)),
  /**
   * How much to prioritize intelligence and capabilities when selecting a model.
   */
  intelligencePriority: optionalType(numberType().min(0).max(1))
}).passthrough();
const SamplingMessageSchema = objectType({
  role: enumType(["user", "assistant"]),
  content: unionType([TextContentSchema, ImageContentSchema, AudioContentSchema])
}).passthrough();
const CreateMessageRequestSchema = RequestSchema.extend({
  method: literalType("sampling/createMessage"),
  params: BaseRequestParamsSchema.extend({
    messages: arrayType(SamplingMessageSchema),
    /**
     * An optional system prompt the server wants to use for sampling. The client MAY modify or omit this prompt.
     */
    systemPrompt: optionalType(stringType()),
    /**
     * A request to include context from one or more MCP servers (including the caller), to be attached to the prompt. The client MAY ignore this request.
     */
    includeContext: optionalType(enumType(["none", "thisServer", "allServers"])),
    temperature: optionalType(numberType()),
    /**
     * The maximum number of tokens to sample, as requested by the server. The client MAY choose to sample fewer tokens than requested.
     */
    maxTokens: numberType().int(),
    stopSequences: optionalType(arrayType(stringType())),
    /**
     * Optional metadata to pass through to the LLM provider. The format of this metadata is provider-specific.
     */
    metadata: optionalType(objectType({}).passthrough()),
    /**
     * The server's preferences for which model to select.
     */
    modelPreferences: optionalType(ModelPreferencesSchema)
  })
});
const CreateMessageResultSchema = ResultSchema.extend({
  /**
   * The name of the model that generated the message.
   */
  model: stringType(),
  /**
   * The reason why sampling stopped.
   */
  stopReason: optionalType(enumType(["endTurn", "stopSequence", "maxTokens"]).or(stringType())),
  role: enumType(["user", "assistant"]),
  content: discriminatedUnionType("type", [TextContentSchema, ImageContentSchema, AudioContentSchema])
});
const BooleanSchemaSchema = objectType({
  type: literalType("boolean"),
  title: optionalType(stringType()),
  description: optionalType(stringType()),
  default: optionalType(booleanType())
}).passthrough();
const StringSchemaSchema = objectType({
  type: literalType("string"),
  title: optionalType(stringType()),
  description: optionalType(stringType()),
  minLength: optionalType(numberType()),
  maxLength: optionalType(numberType()),
  format: optionalType(enumType(["email", "uri", "date", "date-time"]))
}).passthrough();
const NumberSchemaSchema = objectType({
  type: enumType(["number", "integer"]),
  title: optionalType(stringType()),
  description: optionalType(stringType()),
  minimum: optionalType(numberType()),
  maximum: optionalType(numberType())
}).passthrough();
const EnumSchemaSchema = objectType({
  type: literalType("string"),
  title: optionalType(stringType()),
  description: optionalType(stringType()),
  enum: arrayType(stringType()),
  enumNames: optionalType(arrayType(stringType()))
}).passthrough();
const PrimitiveSchemaDefinitionSchema = unionType([BooleanSchemaSchema, StringSchemaSchema, NumberSchemaSchema, EnumSchemaSchema]);
const ElicitRequestSchema = RequestSchema.extend({
  method: literalType("elicitation/create"),
  params: BaseRequestParamsSchema.extend({
    /**
     * The message to present to the user.
     */
    message: stringType(),
    /**
     * The schema for the requested user input.
     */
    requestedSchema: objectType({
      type: literalType("object"),
      properties: recordType(stringType(), PrimitiveSchemaDefinitionSchema),
      required: optionalType(arrayType(stringType()))
    }).passthrough()
  })
});
const ElicitResultSchema = ResultSchema.extend({
  /**
   * The user's response action.
   */
  action: enumType(["accept", "decline", "cancel"]),
  /**
   * The collected user input content (only present if action is "accept").
   */
  content: optionalType(recordType(stringType(), unknownType()))
});
const ResourceTemplateReferenceSchema = objectType({
  type: literalType("ref/resource"),
  /**
   * The URI or URI template of the resource.
   */
  uri: stringType()
}).passthrough();
const PromptReferenceSchema = objectType({
  type: literalType("ref/prompt"),
  /**
   * The name of the prompt or prompt template
   */
  name: stringType()
}).passthrough();
const CompleteRequestSchema = RequestSchema.extend({
  method: literalType("completion/complete"),
  params: BaseRequestParamsSchema.extend({
    ref: unionType([PromptReferenceSchema, ResourceTemplateReferenceSchema]),
    /**
     * The argument's information
     */
    argument: objectType({
      /**
       * The name of the argument
       */
      name: stringType(),
      /**
       * The value of the argument to use for completion matching.
       */
      value: stringType()
    }).passthrough(),
    context: optionalType(objectType({
      /**
       * Previously-resolved variables in a URI template or prompt.
       */
      arguments: optionalType(recordType(stringType(), stringType()))
    }))
  })
});
const CompleteResultSchema = ResultSchema.extend({
  completion: objectType({
    /**
     * An array of completion values. Must not exceed 100 items.
     */
    values: arrayType(stringType()).max(100),
    /**
     * The total number of completion options available. This can exceed the number of values actually sent in the response.
     */
    total: optionalType(numberType().int()),
    /**
     * Indicates whether there are additional completion options beyond those provided in the current response, even if the exact total is unknown.
     */
    hasMore: optionalType(booleanType())
  }).passthrough()
});
const RootSchema = objectType({
  /**
   * The URI identifying the root. This *must* start with file:// for now.
   */
  uri: stringType().startsWith("file://"),
  /**
   * An optional name for the root.
   */
  name: optionalType(stringType()),
  /**
   * See [MCP specification](https://github.com/modelcontextprotocol/modelcontextprotocol/blob/47339c03c143bb4ec01a26e721a1b8fe66634ebe/docs/specification/draft/basic/index.mdx#general-fields)
   * for notes on _meta usage.
   */
  _meta: optionalType(objectType({}).passthrough())
}).passthrough();
const ListRootsRequestSchema = RequestSchema.extend({
  method: literalType("roots/list")
});
const ListRootsResultSchema = ResultSchema.extend({
  roots: arrayType(RootSchema)
});
const RootsListChangedNotificationSchema = NotificationSchema.extend({
  method: literalType("notifications/roots/list_changed")
});
unionType([
  PingRequestSchema,
  InitializeRequestSchema,
  CompleteRequestSchema,
  SetLevelRequestSchema,
  GetPromptRequestSchema,
  ListPromptsRequestSchema,
  ListResourcesRequestSchema,
  ListResourceTemplatesRequestSchema,
  ReadResourceRequestSchema,
  SubscribeRequestSchema,
  UnsubscribeRequestSchema,
  CallToolRequestSchema,
  ListToolsRequestSchema
]);
unionType([
  CancelledNotificationSchema,
  ProgressNotificationSchema,
  InitializedNotificationSchema,
  RootsListChangedNotificationSchema
]);
unionType([EmptyResultSchema, CreateMessageResultSchema, ElicitResultSchema, ListRootsResultSchema]);
unionType([PingRequestSchema, CreateMessageRequestSchema, ElicitRequestSchema, ListRootsRequestSchema]);
unionType([
  CancelledNotificationSchema,
  ProgressNotificationSchema,
  LoggingMessageNotificationSchema,
  ResourceUpdatedNotificationSchema,
  ResourceListChangedNotificationSchema,
  ToolListChangedNotificationSchema,
  PromptListChangedNotificationSchema
]);
unionType([
  EmptyResultSchema,
  InitializeResultSchema,
  CompleteResultSchema,
  GetPromptResultSchema,
  ListPromptsResultSchema,
  ListResourcesResultSchema,
  ListResourceTemplatesResultSchema,
  ReadResourceResultSchema,
  CallToolResultSchema,
  ListToolsResultSchema
]);
class McpError extends Error {
  constructor(code, message, data2) {
    super(`MCP error ${code}: ${message}`);
    this.code = code;
    this.data = data2;
    this.name = "McpError";
  }
}
const DEFAULT_REQUEST_TIMEOUT_MSEC = 6e4;
class Protocol {
  constructor(_options) {
    this._options = _options;
    this._requestMessageId = 0;
    this._requestHandlers = /* @__PURE__ */ new Map();
    this._requestHandlerAbortControllers = /* @__PURE__ */ new Map();
    this._notificationHandlers = /* @__PURE__ */ new Map();
    this._responseHandlers = /* @__PURE__ */ new Map();
    this._progressHandlers = /* @__PURE__ */ new Map();
    this._timeoutInfo = /* @__PURE__ */ new Map();
    this._pendingDebouncedNotifications = /* @__PURE__ */ new Set();
    this.setNotificationHandler(CancelledNotificationSchema, (notification) => {
      const controller = this._requestHandlerAbortControllers.get(notification.params.requestId);
      controller === null || controller === void 0 ? void 0 : controller.abort(notification.params.reason);
    });
    this.setNotificationHandler(ProgressNotificationSchema, (notification) => {
      this._onprogress(notification);
    });
    this.setRequestHandler(
      PingRequestSchema,
      // Automatic pong by default.
      (_request) => ({})
    );
  }
  _setupTimeout(messageId, timeout, maxTotalTimeout, onTimeout, resetTimeoutOnProgress = false) {
    this._timeoutInfo.set(messageId, {
      timeoutId: setTimeout(onTimeout, timeout),
      startTime: Date.now(),
      timeout,
      maxTotalTimeout,
      resetTimeoutOnProgress,
      onTimeout
    });
  }
  _resetTimeout(messageId) {
    const info = this._timeoutInfo.get(messageId);
    if (!info)
      return false;
    const totalElapsed = Date.now() - info.startTime;
    if (info.maxTotalTimeout && totalElapsed >= info.maxTotalTimeout) {
      this._timeoutInfo.delete(messageId);
      throw new McpError(ErrorCode.RequestTimeout, "Maximum total timeout exceeded", {
        maxTotalTimeout: info.maxTotalTimeout,
        totalElapsed
      });
    }
    clearTimeout(info.timeoutId);
    info.timeoutId = setTimeout(info.onTimeout, info.timeout);
    return true;
  }
  _cleanupTimeout(messageId) {
    const info = this._timeoutInfo.get(messageId);
    if (info) {
      clearTimeout(info.timeoutId);
      this._timeoutInfo.delete(messageId);
    }
  }
  /**
   * Attaches to the given transport, starts it, and starts listening for messages.
   *
   * The Protocol object assumes ownership of the Transport, replacing any callbacks that have already been set, and expects that it is the only user of the Transport instance going forward.
   */
  async connect(transport) {
    var _a, _b, _c;
    this._transport = transport;
    const _onclose = (_a = this.transport) === null || _a === void 0 ? void 0 : _a.onclose;
    this._transport.onclose = () => {
      _onclose === null || _onclose === void 0 ? void 0 : _onclose();
      this._onclose();
    };
    const _onerror = (_b = this.transport) === null || _b === void 0 ? void 0 : _b.onerror;
    this._transport.onerror = (error) => {
      _onerror === null || _onerror === void 0 ? void 0 : _onerror(error);
      this._onerror(error);
    };
    const _onmessage = (_c = this._transport) === null || _c === void 0 ? void 0 : _c.onmessage;
    this._transport.onmessage = (message, extra) => {
      _onmessage === null || _onmessage === void 0 ? void 0 : _onmessage(message, extra);
      if (isJSONRPCResponse(message) || isJSONRPCError(message)) {
        this._onresponse(message);
      } else if (isJSONRPCRequest(message)) {
        this._onrequest(message, extra);
      } else if (isJSONRPCNotification(message)) {
        this._onnotification(message);
      } else {
        this._onerror(new Error(`Unknown message type: ${JSON.stringify(message)}`));
      }
    };
    await this._transport.start();
  }
  _onclose() {
    var _a;
    const responseHandlers = this._responseHandlers;
    this._responseHandlers = /* @__PURE__ */ new Map();
    this._progressHandlers.clear();
    this._pendingDebouncedNotifications.clear();
    this._transport = void 0;
    (_a = this.onclose) === null || _a === void 0 ? void 0 : _a.call(this);
    const error = new McpError(ErrorCode.ConnectionClosed, "Connection closed");
    for (const handler of responseHandlers.values()) {
      handler(error);
    }
  }
  _onerror(error) {
    var _a;
    (_a = this.onerror) === null || _a === void 0 ? void 0 : _a.call(this, error);
  }
  _onnotification(notification) {
    var _a;
    const handler = (_a = this._notificationHandlers.get(notification.method)) !== null && _a !== void 0 ? _a : this.fallbackNotificationHandler;
    if (handler === void 0) {
      return;
    }
    Promise.resolve().then(() => handler(notification)).catch((error) => this._onerror(new Error(`Uncaught error in notification handler: ${error}`)));
  }
  _onrequest(request, extra) {
    var _a, _b;
    const handler = (_a = this._requestHandlers.get(request.method)) !== null && _a !== void 0 ? _a : this.fallbackRequestHandler;
    const capturedTransport = this._transport;
    if (handler === void 0) {
      capturedTransport === null || capturedTransport === void 0 ? void 0 : capturedTransport.send({
        jsonrpc: "2.0",
        id: request.id,
        error: {
          code: ErrorCode.MethodNotFound,
          message: "Method not found"
        }
      }).catch((error) => this._onerror(new Error(`Failed to send an error response: ${error}`)));
      return;
    }
    const abortController = new AbortController();
    this._requestHandlerAbortControllers.set(request.id, abortController);
    const fullExtra = {
      signal: abortController.signal,
      sessionId: capturedTransport === null || capturedTransport === void 0 ? void 0 : capturedTransport.sessionId,
      _meta: (_b = request.params) === null || _b === void 0 ? void 0 : _b._meta,
      sendNotification: (notification) => this.notification(notification, { relatedRequestId: request.id }),
      sendRequest: (r2, resultSchema, options) => this.request(r2, resultSchema, { ...options, relatedRequestId: request.id }),
      authInfo: extra === null || extra === void 0 ? void 0 : extra.authInfo,
      requestId: request.id,
      requestInfo: extra === null || extra === void 0 ? void 0 : extra.requestInfo
    };
    Promise.resolve().then(() => handler(request, fullExtra)).then((result) => {
      if (abortController.signal.aborted) {
        return;
      }
      return capturedTransport === null || capturedTransport === void 0 ? void 0 : capturedTransport.send({
        result,
        jsonrpc: "2.0",
        id: request.id
      });
    }, (error) => {
      var _a2;
      if (abortController.signal.aborted) {
        return;
      }
      return capturedTransport === null || capturedTransport === void 0 ? void 0 : capturedTransport.send({
        jsonrpc: "2.0",
        id: request.id,
        error: {
          code: Number.isSafeInteger(error["code"]) ? error["code"] : ErrorCode.InternalError,
          message: (_a2 = error.message) !== null && _a2 !== void 0 ? _a2 : "Internal error"
        }
      });
    }).catch((error) => this._onerror(new Error(`Failed to send response: ${error}`))).finally(() => {
      this._requestHandlerAbortControllers.delete(request.id);
    });
  }
  _onprogress(notification) {
    const { progressToken, ...params } = notification.params;
    const messageId = Number(progressToken);
    const handler = this._progressHandlers.get(messageId);
    if (!handler) {
      this._onerror(new Error(`Received a progress notification for an unknown token: ${JSON.stringify(notification)}`));
      return;
    }
    const responseHandler = this._responseHandlers.get(messageId);
    const timeoutInfo = this._timeoutInfo.get(messageId);
    if (timeoutInfo && responseHandler && timeoutInfo.resetTimeoutOnProgress) {
      try {
        this._resetTimeout(messageId);
      } catch (error) {
        responseHandler(error);
        return;
      }
    }
    handler(params);
  }
  _onresponse(response) {
    const messageId = Number(response.id);
    const handler = this._responseHandlers.get(messageId);
    if (handler === void 0) {
      this._onerror(new Error(`Received a response for an unknown message ID: ${JSON.stringify(response)}`));
      return;
    }
    this._responseHandlers.delete(messageId);
    this._progressHandlers.delete(messageId);
    this._cleanupTimeout(messageId);
    if (isJSONRPCResponse(response)) {
      handler(response);
    } else {
      const error = new McpError(response.error.code, response.error.message, response.error.data);
      handler(error);
    }
  }
  get transport() {
    return this._transport;
  }
  /**
   * Closes the connection.
   */
  async close() {
    var _a;
    await ((_a = this._transport) === null || _a === void 0 ? void 0 : _a.close());
  }
  /**
   * Sends a request and wait for a response.
   *
   * Do not use this method to emit notifications! Use notification() instead.
   */
  request(request, resultSchema, options) {
    const { relatedRequestId, resumptionToken, onresumptiontoken } = options !== null && options !== void 0 ? options : {};
    return new Promise((resolve, reject) => {
      var _a, _b, _c, _d, _e2, _f;
      if (!this._transport) {
        reject(new Error("Not connected"));
        return;
      }
      if (((_a = this._options) === null || _a === void 0 ? void 0 : _a.enforceStrictCapabilities) === true) {
        this.assertCapabilityForMethod(request.method);
      }
      (_b = options === null || options === void 0 ? void 0 : options.signal) === null || _b === void 0 ? void 0 : _b.throwIfAborted();
      const messageId = this._requestMessageId++;
      const jsonrpcRequest = {
        ...request,
        jsonrpc: "2.0",
        id: messageId
      };
      if (options === null || options === void 0 ? void 0 : options.onprogress) {
        this._progressHandlers.set(messageId, options.onprogress);
        jsonrpcRequest.params = {
          ...request.params,
          _meta: {
            ...((_c = request.params) === null || _c === void 0 ? void 0 : _c._meta) || {},
            progressToken: messageId
          }
        };
      }
      const cancel = (reason) => {
        var _a2;
        this._responseHandlers.delete(messageId);
        this._progressHandlers.delete(messageId);
        this._cleanupTimeout(messageId);
        (_a2 = this._transport) === null || _a2 === void 0 ? void 0 : _a2.send({
          jsonrpc: "2.0",
          method: "notifications/cancelled",
          params: {
            requestId: messageId,
            reason: String(reason)
          }
        }, { relatedRequestId, resumptionToken, onresumptiontoken }).catch((error) => this._onerror(new Error(`Failed to send cancellation: ${error}`)));
        reject(reason);
      };
      this._responseHandlers.set(messageId, (response) => {
        var _a2;
        if ((_a2 = options === null || options === void 0 ? void 0 : options.signal) === null || _a2 === void 0 ? void 0 : _a2.aborted) {
          return;
        }
        if (response instanceof Error) {
          return reject(response);
        }
        try {
          const result = resultSchema.parse(response.result);
          resolve(result);
        } catch (error) {
          reject(error);
        }
      });
      (_d = options === null || options === void 0 ? void 0 : options.signal) === null || _d === void 0 ? void 0 : _d.addEventListener("abort", () => {
        var _a2;
        cancel((_a2 = options === null || options === void 0 ? void 0 : options.signal) === null || _a2 === void 0 ? void 0 : _a2.reason);
      });
      const timeout = (_e2 = options === null || options === void 0 ? void 0 : options.timeout) !== null && _e2 !== void 0 ? _e2 : DEFAULT_REQUEST_TIMEOUT_MSEC;
      const timeoutHandler = () => cancel(new McpError(ErrorCode.RequestTimeout, "Request timed out", { timeout }));
      this._setupTimeout(messageId, timeout, options === null || options === void 0 ? void 0 : options.maxTotalTimeout, timeoutHandler, (_f = options === null || options === void 0 ? void 0 : options.resetTimeoutOnProgress) !== null && _f !== void 0 ? _f : false);
      this._transport.send(jsonrpcRequest, { relatedRequestId, resumptionToken, onresumptiontoken }).catch((error) => {
        this._cleanupTimeout(messageId);
        reject(error);
      });
    });
  }
  /**
   * Emits a notification, which is a one-way message that does not expect a response.
   */
  async notification(notification, options) {
    var _a, _b;
    if (!this._transport) {
      throw new Error("Not connected");
    }
    this.assertNotificationCapability(notification.method);
    const debouncedMethods = (_b = (_a = this._options) === null || _a === void 0 ? void 0 : _a.debouncedNotificationMethods) !== null && _b !== void 0 ? _b : [];
    const canDebounce = debouncedMethods.includes(notification.method) && !notification.params && !(options === null || options === void 0 ? void 0 : options.relatedRequestId);
    if (canDebounce) {
      if (this._pendingDebouncedNotifications.has(notification.method)) {
        return;
      }
      this._pendingDebouncedNotifications.add(notification.method);
      Promise.resolve().then(() => {
        var _a2;
        this._pendingDebouncedNotifications.delete(notification.method);
        if (!this._transport) {
          return;
        }
        const jsonrpcNotification2 = {
          ...notification,
          jsonrpc: "2.0"
        };
        (_a2 = this._transport) === null || _a2 === void 0 ? void 0 : _a2.send(jsonrpcNotification2, options).catch((error) => this._onerror(error));
      });
      return;
    }
    const jsonrpcNotification = {
      ...notification,
      jsonrpc: "2.0"
    };
    await this._transport.send(jsonrpcNotification, options);
  }
  /**
   * Registers a handler to invoke when this protocol object receives a request with the given method.
   *
   * Note that this will replace any previous request handler for the same method.
   */
  setRequestHandler(requestSchema, handler) {
    const method = requestSchema.shape.method.value;
    this.assertRequestHandlerCapability(method);
    this._requestHandlers.set(method, (request, extra) => {
      return Promise.resolve(handler(requestSchema.parse(request), extra));
    });
  }
  /**
   * Removes the request handler for the given method.
   */
  removeRequestHandler(method) {
    this._requestHandlers.delete(method);
  }
  /**
   * Asserts that a request handler has not already been set for the given method, in preparation for a new one being automatically installed.
   */
  assertCanSetRequestHandler(method) {
    if (this._requestHandlers.has(method)) {
      throw new Error(`A request handler for ${method} already exists, which would be overridden`);
    }
  }
  /**
   * Registers a handler to invoke when this protocol object receives a notification with the given method.
   *
   * Note that this will replace any previous notification handler for the same method.
   */
  setNotificationHandler(notificationSchema, handler) {
    this._notificationHandlers.set(notificationSchema.shape.method.value, (notification) => Promise.resolve(handler(notificationSchema.parse(notification))));
  }
  /**
   * Removes the notification handler for the given method.
   */
  removeNotificationHandler(method) {
    this._notificationHandlers.delete(method);
  }
}
function mergeCapabilities(base, additional) {
  return Object.entries(additional).reduce((acc, [key, value]) => {
    if (value && typeof value === "object") {
      acc[key] = acc[key] ? { ...acc[key], ...value } : value;
    } else {
      acc[key] = value;
    }
    return acc;
  }, { ...base });
}
function getDefaultExportFromCjs(x2) {
  return x2 && x2.__esModule && Object.prototype.hasOwnProperty.call(x2, "default") ? x2["default"] : x2;
}
var uri_all$1 = { exports: {} };
/** @license URI.js v4.4.1 (c) 2011 Gary Court. License: http://github.com/garycourt/uri-js */
var uri_all = uri_all$1.exports;
var hasRequiredUri_all;
function requireUri_all() {
  if (hasRequiredUri_all) return uri_all$1.exports;
  hasRequiredUri_all = 1;
  (function(module, exports) {
    (function(global, factory) {
      factory(exports);
    })(uri_all, (function(exports2) {
      function merge() {
        for (var _len = arguments.length, sets = Array(_len), _key = 0; _key < _len; _key++) {
          sets[_key] = arguments[_key];
        }
        if (sets.length > 1) {
          sets[0] = sets[0].slice(0, -1);
          var xl = sets.length - 1;
          for (var x2 = 1; x2 < xl; ++x2) {
            sets[x2] = sets[x2].slice(1, -1);
          }
          sets[xl] = sets[xl].slice(1);
          return sets.join("");
        } else {
          return sets[0];
        }
      }
      function subexp(str) {
        return "(?:" + str + ")";
      }
      function typeOf(o2) {
        return o2 === void 0 ? "undefined" : o2 === null ? "null" : Object.prototype.toString.call(o2).split(" ").pop().split("]").shift().toLowerCase();
      }
      function toUpperCase(str) {
        return str.toUpperCase();
      }
      function toArray(obj) {
        return obj !== void 0 && obj !== null ? obj instanceof Array ? obj : typeof obj.length !== "number" || obj.split || obj.setInterval || obj.call ? [obj] : Array.prototype.slice.call(obj) : [];
      }
      function assign(target, source) {
        var obj = target;
        if (source) {
          for (var key in source) {
            obj[key] = source[key];
          }
        }
        return obj;
      }
      function buildExps(isIRI) {
        var ALPHA$$ = "[A-Za-z]", DIGIT$$ = "[0-9]", HEXDIG$$2 = merge(DIGIT$$, "[A-Fa-f]"), PCT_ENCODED$2 = subexp(subexp("%[EFef]" + HEXDIG$$2 + "%" + HEXDIG$$2 + HEXDIG$$2 + "%" + HEXDIG$$2 + HEXDIG$$2) + "|" + subexp("%[89A-Fa-f]" + HEXDIG$$2 + "%" + HEXDIG$$2 + HEXDIG$$2) + "|" + subexp("%" + HEXDIG$$2 + HEXDIG$$2)), GEN_DELIMS$$ = "[\\:\\/\\?\\#\\[\\]\\@]", SUB_DELIMS$$ = "[\\!\\$\\&\\'\\(\\)\\*\\+\\,\\;\\=]", RESERVED$$ = merge(GEN_DELIMS$$, SUB_DELIMS$$), UCSCHAR$$ = isIRI ? "[\\xA0-\\u200D\\u2010-\\u2029\\u202F-\\uD7FF\\uF900-\\uFDCF\\uFDF0-\\uFFEF]" : "[]", IPRIVATE$$ = isIRI ? "[\\uE000-\\uF8FF]" : "[]", UNRESERVED$$2 = merge(ALPHA$$, DIGIT$$, "[\\-\\.\\_\\~]", UCSCHAR$$);
        subexp(ALPHA$$ + merge(ALPHA$$, DIGIT$$, "[\\+\\-\\.]") + "*");
        subexp(subexp(PCT_ENCODED$2 + "|" + merge(UNRESERVED$$2, SUB_DELIMS$$, "[\\:]")) + "*");
        var DEC_OCTET_RELAXED$ = subexp(subexp("25[0-5]") + "|" + subexp("2[0-4]" + DIGIT$$) + "|" + subexp("1" + DIGIT$$ + DIGIT$$) + "|" + subexp("0?[1-9]" + DIGIT$$) + "|0?0?" + DIGIT$$), IPV4ADDRESS$ = subexp(DEC_OCTET_RELAXED$ + "\\." + DEC_OCTET_RELAXED$ + "\\." + DEC_OCTET_RELAXED$ + "\\." + DEC_OCTET_RELAXED$), H16$ = subexp(HEXDIG$$2 + "{1,4}"), LS32$ = subexp(subexp(H16$ + "\\:" + H16$) + "|" + IPV4ADDRESS$), IPV6ADDRESS1$ = subexp(subexp(H16$ + "\\:") + "{6}" + LS32$), IPV6ADDRESS2$ = subexp("\\:\\:" + subexp(H16$ + "\\:") + "{5}" + LS32$), IPV6ADDRESS3$ = subexp(subexp(H16$) + "?\\:\\:" + subexp(H16$ + "\\:") + "{4}" + LS32$), IPV6ADDRESS4$ = subexp(subexp(subexp(H16$ + "\\:") + "{0,1}" + H16$) + "?\\:\\:" + subexp(H16$ + "\\:") + "{3}" + LS32$), IPV6ADDRESS5$ = subexp(subexp(subexp(H16$ + "\\:") + "{0,2}" + H16$) + "?\\:\\:" + subexp(H16$ + "\\:") + "{2}" + LS32$), IPV6ADDRESS6$ = subexp(subexp(subexp(H16$ + "\\:") + "{0,3}" + H16$) + "?\\:\\:" + H16$ + "\\:" + LS32$), IPV6ADDRESS7$ = subexp(subexp(subexp(H16$ + "\\:") + "{0,4}" + H16$) + "?\\:\\:" + LS32$), IPV6ADDRESS8$ = subexp(subexp(subexp(H16$ + "\\:") + "{0,5}" + H16$) + "?\\:\\:" + H16$), IPV6ADDRESS9$ = subexp(subexp(subexp(H16$ + "\\:") + "{0,6}" + H16$) + "?\\:\\:"), IPV6ADDRESS$ = subexp([IPV6ADDRESS1$, IPV6ADDRESS2$, IPV6ADDRESS3$, IPV6ADDRESS4$, IPV6ADDRESS5$, IPV6ADDRESS6$, IPV6ADDRESS7$, IPV6ADDRESS8$, IPV6ADDRESS9$].join("|")), ZONEID$ = subexp(subexp(UNRESERVED$$2 + "|" + PCT_ENCODED$2) + "+");
        subexp("[vV]" + HEXDIG$$2 + "+\\." + merge(UNRESERVED$$2, SUB_DELIMS$$, "[\\:]") + "+");
        subexp(subexp(PCT_ENCODED$2 + "|" + merge(UNRESERVED$$2, SUB_DELIMS$$)) + "*");
        var PCHAR$ = subexp(PCT_ENCODED$2 + "|" + merge(UNRESERVED$$2, SUB_DELIMS$$, "[\\:\\@]"));
        subexp(subexp(PCT_ENCODED$2 + "|" + merge(UNRESERVED$$2, SUB_DELIMS$$, "[\\@]")) + "+");
        subexp(subexp(PCHAR$ + "|" + merge("[\\/\\?]", IPRIVATE$$)) + "*");
        return {
          NOT_SCHEME: new RegExp(merge("[^]", ALPHA$$, DIGIT$$, "[\\+\\-\\.]"), "g"),
          NOT_USERINFO: new RegExp(merge("[^\\%\\:]", UNRESERVED$$2, SUB_DELIMS$$), "g"),
          NOT_HOST: new RegExp(merge("[^\\%\\[\\]\\:]", UNRESERVED$$2, SUB_DELIMS$$), "g"),
          NOT_PATH: new RegExp(merge("[^\\%\\/\\:\\@]", UNRESERVED$$2, SUB_DELIMS$$), "g"),
          NOT_PATH_NOSCHEME: new RegExp(merge("[^\\%\\/\\@]", UNRESERVED$$2, SUB_DELIMS$$), "g"),
          NOT_QUERY: new RegExp(merge("[^\\%]", UNRESERVED$$2, SUB_DELIMS$$, "[\\:\\@\\/\\?]", IPRIVATE$$), "g"),
          NOT_FRAGMENT: new RegExp(merge("[^\\%]", UNRESERVED$$2, SUB_DELIMS$$, "[\\:\\@\\/\\?]"), "g"),
          ESCAPE: new RegExp(merge("[^]", UNRESERVED$$2, SUB_DELIMS$$), "g"),
          UNRESERVED: new RegExp(UNRESERVED$$2, "g"),
          OTHER_CHARS: new RegExp(merge("[^\\%]", UNRESERVED$$2, RESERVED$$), "g"),
          PCT_ENCODED: new RegExp(PCT_ENCODED$2, "g"),
          IPV4ADDRESS: new RegExp("^(" + IPV4ADDRESS$ + ")$"),
          IPV6ADDRESS: new RegExp("^\\[?(" + IPV6ADDRESS$ + ")" + subexp(subexp("\\%25|\\%(?!" + HEXDIG$$2 + "{2})") + "(" + ZONEID$ + ")") + "?\\]?$")
          //RFC 6874, with relaxed parsing rules
        };
      }
      var URI_PROTOCOL = buildExps(false);
      var IRI_PROTOCOL = buildExps(true);
      var slicedToArray = /* @__PURE__ */ (function() {
        function sliceIterator(arr, i2) {
          var _arr = [];
          var _n = true;
          var _d = false;
          var _e2 = void 0;
          try {
            for (var _i = arr[Symbol.iterator](), _s2; !(_n = (_s2 = _i.next()).done); _n = true) {
              _arr.push(_s2.value);
              if (i2 && _arr.length === i2) break;
            }
          } catch (err) {
            _d = true;
            _e2 = err;
          } finally {
            try {
              if (!_n && _i["return"]) _i["return"]();
            } finally {
              if (_d) throw _e2;
            }
          }
          return _arr;
        }
        return function(arr, i2) {
          if (Array.isArray(arr)) {
            return arr;
          } else if (Symbol.iterator in Object(arr)) {
            return sliceIterator(arr, i2);
          } else {
            throw new TypeError("Invalid attempt to destructure non-iterable instance");
          }
        };
      })();
      var toConsumableArray = function(arr) {
        if (Array.isArray(arr)) {
          for (var i2 = 0, arr2 = Array(arr.length); i2 < arr.length; i2++) arr2[i2] = arr[i2];
          return arr2;
        } else {
          return Array.from(arr);
        }
      };
      var maxInt = 2147483647;
      var base = 36;
      var tMin = 1;
      var tMax = 26;
      var skew = 38;
      var damp = 700;
      var initialBias = 72;
      var initialN = 128;
      var delimiter = "-";
      var regexPunycode = /^xn--/;
      var regexNonASCII = /[^\0-\x7E]/;
      var regexSeparators = /[\x2E\u3002\uFF0E\uFF61]/g;
      var errors = {
        "overflow": "Overflow: input needs wider integers to process",
        "not-basic": "Illegal input >= 0x80 (not a basic code point)",
        "invalid-input": "Invalid input"
      };
      var baseMinusTMin = base - tMin;
      var floor = Math.floor;
      var stringFromCharCode = String.fromCharCode;
      function error$1(type2) {
        throw new RangeError(errors[type2]);
      }
      function map(array, fn) {
        var result = [];
        var length = array.length;
        while (length--) {
          result[length] = fn(array[length]);
        }
        return result;
      }
      function mapDomain(string, fn) {
        var parts = string.split("@");
        var result = "";
        if (parts.length > 1) {
          result = parts[0] + "@";
          string = parts[1];
        }
        string = string.replace(regexSeparators, ".");
        var labels = string.split(".");
        var encoded = map(labels, fn).join(".");
        return result + encoded;
      }
      function ucs2decode(string) {
        var output = [];
        var counter = 0;
        var length = string.length;
        while (counter < length) {
          var value = string.charCodeAt(counter++);
          if (value >= 55296 && value <= 56319 && counter < length) {
            var extra = string.charCodeAt(counter++);
            if ((extra & 64512) == 56320) {
              output.push(((value & 1023) << 10) + (extra & 1023) + 65536);
            } else {
              output.push(value);
              counter--;
            }
          } else {
            output.push(value);
          }
        }
        return output;
      }
      var ucs2encode = function ucs2encode2(array) {
        return String.fromCodePoint.apply(String, toConsumableArray(array));
      };
      var basicToDigit = function basicToDigit2(codePoint) {
        if (codePoint - 48 < 10) {
          return codePoint - 22;
        }
        if (codePoint - 65 < 26) {
          return codePoint - 65;
        }
        if (codePoint - 97 < 26) {
          return codePoint - 97;
        }
        return base;
      };
      var digitToBasic = function digitToBasic2(digit, flag) {
        return digit + 22 + 75 * (digit < 26) - ((flag != 0) << 5);
      };
      var adapt = function adapt2(delta, numPoints, firstTime) {
        var k2 = 0;
        delta = firstTime ? floor(delta / damp) : delta >> 1;
        delta += floor(delta / numPoints);
        for (
          ;
          /* no initialization */
          delta > baseMinusTMin * tMax >> 1;
          k2 += base
        ) {
          delta = floor(delta / baseMinusTMin);
        }
        return floor(k2 + (baseMinusTMin + 1) * delta / (delta + skew));
      };
      var decode = function decode2(input) {
        var output = [];
        var inputLength = input.length;
        var i2 = 0;
        var n2 = initialN;
        var bias = initialBias;
        var basic = input.lastIndexOf(delimiter);
        if (basic < 0) {
          basic = 0;
        }
        for (var j2 = 0; j2 < basic; ++j2) {
          if (input.charCodeAt(j2) >= 128) {
            error$1("not-basic");
          }
          output.push(input.charCodeAt(j2));
        }
        for (var index = basic > 0 ? basic + 1 : 0; index < inputLength; ) {
          var oldi = i2;
          for (
            var w2 = 1, k2 = base;
            ;
            /* no condition */
            k2 += base
          ) {
            if (index >= inputLength) {
              error$1("invalid-input");
            }
            var digit = basicToDigit(input.charCodeAt(index++));
            if (digit >= base || digit > floor((maxInt - i2) / w2)) {
              error$1("overflow");
            }
            i2 += digit * w2;
            var t2 = k2 <= bias ? tMin : k2 >= bias + tMax ? tMax : k2 - bias;
            if (digit < t2) {
              break;
            }
            var baseMinusT = base - t2;
            if (w2 > floor(maxInt / baseMinusT)) {
              error$1("overflow");
            }
            w2 *= baseMinusT;
          }
          var out = output.length + 1;
          bias = adapt(i2 - oldi, out, oldi == 0);
          if (floor(i2 / out) > maxInt - n2) {
            error$1("overflow");
          }
          n2 += floor(i2 / out);
          i2 %= out;
          output.splice(i2++, 0, n2);
        }
        return String.fromCodePoint.apply(String, output);
      };
      var encode = function encode2(input) {
        var output = [];
        input = ucs2decode(input);
        var inputLength = input.length;
        var n2 = initialN;
        var delta = 0;
        var bias = initialBias;
        var _iteratorNormalCompletion = true;
        var _didIteratorError = false;
        var _iteratorError = void 0;
        try {
          for (var _iterator = input[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
            var _currentValue2 = _step.value;
            if (_currentValue2 < 128) {
              output.push(stringFromCharCode(_currentValue2));
            }
          }
        } catch (err) {
          _didIteratorError = true;
          _iteratorError = err;
        } finally {
          try {
            if (!_iteratorNormalCompletion && _iterator.return) {
              _iterator.return();
            }
          } finally {
            if (_didIteratorError) {
              throw _iteratorError;
            }
          }
        }
        var basicLength = output.length;
        var handledCPCount = basicLength;
        if (basicLength) {
          output.push(delimiter);
        }
        while (handledCPCount < inputLength) {
          var m2 = maxInt;
          var _iteratorNormalCompletion2 = true;
          var _didIteratorError2 = false;
          var _iteratorError2 = void 0;
          try {
            for (var _iterator2 = input[Symbol.iterator](), _step2; !(_iteratorNormalCompletion2 = (_step2 = _iterator2.next()).done); _iteratorNormalCompletion2 = true) {
              var currentValue = _step2.value;
              if (currentValue >= n2 && currentValue < m2) {
                m2 = currentValue;
              }
            }
          } catch (err) {
            _didIteratorError2 = true;
            _iteratorError2 = err;
          } finally {
            try {
              if (!_iteratorNormalCompletion2 && _iterator2.return) {
                _iterator2.return();
              }
            } finally {
              if (_didIteratorError2) {
                throw _iteratorError2;
              }
            }
          }
          var handledCPCountPlusOne = handledCPCount + 1;
          if (m2 - n2 > floor((maxInt - delta) / handledCPCountPlusOne)) {
            error$1("overflow");
          }
          delta += (m2 - n2) * handledCPCountPlusOne;
          n2 = m2;
          var _iteratorNormalCompletion3 = true;
          var _didIteratorError3 = false;
          var _iteratorError3 = void 0;
          try {
            for (var _iterator3 = input[Symbol.iterator](), _step3; !(_iteratorNormalCompletion3 = (_step3 = _iterator3.next()).done); _iteratorNormalCompletion3 = true) {
              var _currentValue = _step3.value;
              if (_currentValue < n2 && ++delta > maxInt) {
                error$1("overflow");
              }
              if (_currentValue == n2) {
                var q2 = delta;
                for (
                  var k2 = base;
                  ;
                  /* no condition */
                  k2 += base
                ) {
                  var t2 = k2 <= bias ? tMin : k2 >= bias + tMax ? tMax : k2 - bias;
                  if (q2 < t2) {
                    break;
                  }
                  var qMinusT = q2 - t2;
                  var baseMinusT = base - t2;
                  output.push(stringFromCharCode(digitToBasic(t2 + qMinusT % baseMinusT, 0)));
                  q2 = floor(qMinusT / baseMinusT);
                }
                output.push(stringFromCharCode(digitToBasic(q2, 0)));
                bias = adapt(delta, handledCPCountPlusOne, handledCPCount == basicLength);
                delta = 0;
                ++handledCPCount;
              }
            }
          } catch (err) {
            _didIteratorError3 = true;
            _iteratorError3 = err;
          } finally {
            try {
              if (!_iteratorNormalCompletion3 && _iterator3.return) {
                _iterator3.return();
              }
            } finally {
              if (_didIteratorError3) {
                throw _iteratorError3;
              }
            }
          }
          ++delta;
          ++n2;
        }
        return output.join("");
      };
      var toUnicode = function toUnicode2(input) {
        return mapDomain(input, function(string) {
          return regexPunycode.test(string) ? decode(string.slice(4).toLowerCase()) : string;
        });
      };
      var toASCII = function toASCII2(input) {
        return mapDomain(input, function(string) {
          return regexNonASCII.test(string) ? "xn--" + encode(string) : string;
        });
      };
      var punycode = {
        /**
         * A string representing the current Punycode.js version number.
         * @memberOf punycode
         * @type String
         */
        "version": "2.1.0",
        /**
         * An object of methods to convert from JavaScript's internal character
         * representation (UCS-2) to Unicode code points, and back.
         * @see <https://mathiasbynens.be/notes/javascript-encoding>
         * @memberOf punycode
         * @type Object
         */
        "ucs2": {
          "decode": ucs2decode,
          "encode": ucs2encode
        },
        "decode": decode,
        "encode": encode,
        "toASCII": toASCII,
        "toUnicode": toUnicode
      };
      var SCHEMES = {};
      function pctEncChar(chr) {
        var c2 = chr.charCodeAt(0);
        var e2 = void 0;
        if (c2 < 16) e2 = "%0" + c2.toString(16).toUpperCase();
        else if (c2 < 128) e2 = "%" + c2.toString(16).toUpperCase();
        else if (c2 < 2048) e2 = "%" + (c2 >> 6 | 192).toString(16).toUpperCase() + "%" + (c2 & 63 | 128).toString(16).toUpperCase();
        else e2 = "%" + (c2 >> 12 | 224).toString(16).toUpperCase() + "%" + (c2 >> 6 & 63 | 128).toString(16).toUpperCase() + "%" + (c2 & 63 | 128).toString(16).toUpperCase();
        return e2;
      }
      function pctDecChars(str) {
        var newStr = "";
        var i2 = 0;
        var il = str.length;
        while (i2 < il) {
          var c2 = parseInt(str.substr(i2 + 1, 2), 16);
          if (c2 < 128) {
            newStr += String.fromCharCode(c2);
            i2 += 3;
          } else if (c2 >= 194 && c2 < 224) {
            if (il - i2 >= 6) {
              var c22 = parseInt(str.substr(i2 + 4, 2), 16);
              newStr += String.fromCharCode((c2 & 31) << 6 | c22 & 63);
            } else {
              newStr += str.substr(i2, 6);
            }
            i2 += 6;
          } else if (c2 >= 224) {
            if (il - i2 >= 9) {
              var _c = parseInt(str.substr(i2 + 4, 2), 16);
              var c3 = parseInt(str.substr(i2 + 7, 2), 16);
              newStr += String.fromCharCode((c2 & 15) << 12 | (_c & 63) << 6 | c3 & 63);
            } else {
              newStr += str.substr(i2, 9);
            }
            i2 += 9;
          } else {
            newStr += str.substr(i2, 3);
            i2 += 3;
          }
        }
        return newStr;
      }
      function _normalizeComponentEncoding(components, protocol) {
        function decodeUnreserved2(str) {
          var decStr = pctDecChars(str);
          return !decStr.match(protocol.UNRESERVED) ? str : decStr;
        }
        if (components.scheme) components.scheme = String(components.scheme).replace(protocol.PCT_ENCODED, decodeUnreserved2).toLowerCase().replace(protocol.NOT_SCHEME, "");
        if (components.userinfo !== void 0) components.userinfo = String(components.userinfo).replace(protocol.PCT_ENCODED, decodeUnreserved2).replace(protocol.NOT_USERINFO, pctEncChar).replace(protocol.PCT_ENCODED, toUpperCase);
        if (components.host !== void 0) components.host = String(components.host).replace(protocol.PCT_ENCODED, decodeUnreserved2).toLowerCase().replace(protocol.NOT_HOST, pctEncChar).replace(protocol.PCT_ENCODED, toUpperCase);
        if (components.path !== void 0) components.path = String(components.path).replace(protocol.PCT_ENCODED, decodeUnreserved2).replace(components.scheme ? protocol.NOT_PATH : protocol.NOT_PATH_NOSCHEME, pctEncChar).replace(protocol.PCT_ENCODED, toUpperCase);
        if (components.query !== void 0) components.query = String(components.query).replace(protocol.PCT_ENCODED, decodeUnreserved2).replace(protocol.NOT_QUERY, pctEncChar).replace(protocol.PCT_ENCODED, toUpperCase);
        if (components.fragment !== void 0) components.fragment = String(components.fragment).replace(protocol.PCT_ENCODED, decodeUnreserved2).replace(protocol.NOT_FRAGMENT, pctEncChar).replace(protocol.PCT_ENCODED, toUpperCase);
        return components;
      }
      function _stripLeadingZeros(str) {
        return str.replace(/^0*(.*)/, "$1") || "0";
      }
      function _normalizeIPv4(host, protocol) {
        var matches = host.match(protocol.IPV4ADDRESS) || [];
        var _matches = slicedToArray(matches, 2), address = _matches[1];
        if (address) {
          return address.split(".").map(_stripLeadingZeros).join(".");
        } else {
          return host;
        }
      }
      function _normalizeIPv6(host, protocol) {
        var matches = host.match(protocol.IPV6ADDRESS) || [];
        var _matches2 = slicedToArray(matches, 3), address = _matches2[1], zone = _matches2[2];
        if (address) {
          var _address$toLowerCase$ = address.toLowerCase().split("::").reverse(), _address$toLowerCase$2 = slicedToArray(_address$toLowerCase$, 2), last = _address$toLowerCase$2[0], first = _address$toLowerCase$2[1];
          var firstFields = first ? first.split(":").map(_stripLeadingZeros) : [];
          var lastFields = last.split(":").map(_stripLeadingZeros);
          var isLastFieldIPv4Address = protocol.IPV4ADDRESS.test(lastFields[lastFields.length - 1]);
          var fieldCount = isLastFieldIPv4Address ? 7 : 8;
          var lastFieldsStart = lastFields.length - fieldCount;
          var fields = Array(fieldCount);
          for (var x2 = 0; x2 < fieldCount; ++x2) {
            fields[x2] = firstFields[x2] || lastFields[lastFieldsStart + x2] || "";
          }
          if (isLastFieldIPv4Address) {
            fields[fieldCount - 1] = _normalizeIPv4(fields[fieldCount - 1], protocol);
          }
          var allZeroFields = fields.reduce(function(acc, field, index) {
            if (!field || field === "0") {
              var lastLongest = acc[acc.length - 1];
              if (lastLongest && lastLongest.index + lastLongest.length === index) {
                lastLongest.length++;
              } else {
                acc.push({ index, length: 1 });
              }
            }
            return acc;
          }, []);
          var longestZeroFields = allZeroFields.sort(function(a2, b2) {
            return b2.length - a2.length;
          })[0];
          var newHost = void 0;
          if (longestZeroFields && longestZeroFields.length > 1) {
            var newFirst = fields.slice(0, longestZeroFields.index);
            var newLast = fields.slice(longestZeroFields.index + longestZeroFields.length);
            newHost = newFirst.join(":") + "::" + newLast.join(":");
          } else {
            newHost = fields.join(":");
          }
          if (zone) {
            newHost += "%" + zone;
          }
          return newHost;
        } else {
          return host;
        }
      }
      var URI_PARSE = /^(?:([^:\/?#]+):)?(?:\/\/((?:([^\/?#@]*)@)?(\[[^\/?#\]]+\]|[^\/?#:]*)(?:\:(\d*))?))?([^?#]*)(?:\?([^#]*))?(?:#((?:.|\n|\r)*))?/i;
      var NO_MATCH_IS_UNDEFINED = "".match(/(){0}/)[1] === void 0;
      function parse(uriString) {
        var options = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : {};
        var components = {};
        var protocol = options.iri !== false ? IRI_PROTOCOL : URI_PROTOCOL;
        if (options.reference === "suffix") uriString = (options.scheme ? options.scheme + ":" : "") + "//" + uriString;
        var matches = uriString.match(URI_PARSE);
        if (matches) {
          if (NO_MATCH_IS_UNDEFINED) {
            components.scheme = matches[1];
            components.userinfo = matches[3];
            components.host = matches[4];
            components.port = parseInt(matches[5], 10);
            components.path = matches[6] || "";
            components.query = matches[7];
            components.fragment = matches[8];
            if (isNaN(components.port)) {
              components.port = matches[5];
            }
          } else {
            components.scheme = matches[1] || void 0;
            components.userinfo = uriString.indexOf("@") !== -1 ? matches[3] : void 0;
            components.host = uriString.indexOf("//") !== -1 ? matches[4] : void 0;
            components.port = parseInt(matches[5], 10);
            components.path = matches[6] || "";
            components.query = uriString.indexOf("?") !== -1 ? matches[7] : void 0;
            components.fragment = uriString.indexOf("#") !== -1 ? matches[8] : void 0;
            if (isNaN(components.port)) {
              components.port = uriString.match(/\/\/(?:.|\n)*\:(?:\/|\?|\#|$)/) ? matches[4] : void 0;
            }
          }
          if (components.host) {
            components.host = _normalizeIPv6(_normalizeIPv4(components.host, protocol), protocol);
          }
          if (components.scheme === void 0 && components.userinfo === void 0 && components.host === void 0 && components.port === void 0 && !components.path && components.query === void 0) {
            components.reference = "same-document";
          } else if (components.scheme === void 0) {
            components.reference = "relative";
          } else if (components.fragment === void 0) {
            components.reference = "absolute";
          } else {
            components.reference = "uri";
          }
          if (options.reference && options.reference !== "suffix" && options.reference !== components.reference) {
            components.error = components.error || "URI is not a " + options.reference + " reference.";
          }
          var schemeHandler = SCHEMES[(options.scheme || components.scheme || "").toLowerCase()];
          if (!options.unicodeSupport && (!schemeHandler || !schemeHandler.unicodeSupport)) {
            if (components.host && (options.domainHost || schemeHandler && schemeHandler.domainHost)) {
              try {
                components.host = punycode.toASCII(components.host.replace(protocol.PCT_ENCODED, pctDecChars).toLowerCase());
              } catch (e2) {
                components.error = components.error || "Host's domain name can not be converted to ASCII via punycode: " + e2;
              }
            }
            _normalizeComponentEncoding(components, URI_PROTOCOL);
          } else {
            _normalizeComponentEncoding(components, protocol);
          }
          if (schemeHandler && schemeHandler.parse) {
            schemeHandler.parse(components, options);
          }
        } else {
          components.error = components.error || "URI can not be parsed.";
        }
        return components;
      }
      function _recomposeAuthority(components, options) {
        var protocol = options.iri !== false ? IRI_PROTOCOL : URI_PROTOCOL;
        var uriTokens = [];
        if (components.userinfo !== void 0) {
          uriTokens.push(components.userinfo);
          uriTokens.push("@");
        }
        if (components.host !== void 0) {
          uriTokens.push(_normalizeIPv6(_normalizeIPv4(String(components.host), protocol), protocol).replace(protocol.IPV6ADDRESS, function(_2, $1, $2) {
            return "[" + $1 + ($2 ? "%25" + $2 : "") + "]";
          }));
        }
        if (typeof components.port === "number" || typeof components.port === "string") {
          uriTokens.push(":");
          uriTokens.push(String(components.port));
        }
        return uriTokens.length ? uriTokens.join("") : void 0;
      }
      var RDS1 = /^\.\.?\//;
      var RDS2 = /^\/\.(\/|$)/;
      var RDS3 = /^\/\.\.(\/|$)/;
      var RDS5 = /^\/?(?:.|\n)*?(?=\/|$)/;
      function removeDotSegments(input) {
        var output = [];
        while (input.length) {
          if (input.match(RDS1)) {
            input = input.replace(RDS1, "");
          } else if (input.match(RDS2)) {
            input = input.replace(RDS2, "/");
          } else if (input.match(RDS3)) {
            input = input.replace(RDS3, "/");
            output.pop();
          } else if (input === "." || input === "..") {
            input = "";
          } else {
            var im = input.match(RDS5);
            if (im) {
              var s2 = im[0];
              input = input.slice(s2.length);
              output.push(s2);
            } else {
              throw new Error("Unexpected dot segment condition");
            }
          }
        }
        return output.join("");
      }
      function serialize(components) {
        var options = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : {};
        var protocol = options.iri ? IRI_PROTOCOL : URI_PROTOCOL;
        var uriTokens = [];
        var schemeHandler = SCHEMES[(options.scheme || components.scheme || "").toLowerCase()];
        if (schemeHandler && schemeHandler.serialize) schemeHandler.serialize(components, options);
        if (components.host) {
          if (protocol.IPV6ADDRESS.test(components.host)) ;
          else if (options.domainHost || schemeHandler && schemeHandler.domainHost) {
            try {
              components.host = !options.iri ? punycode.toASCII(components.host.replace(protocol.PCT_ENCODED, pctDecChars).toLowerCase()) : punycode.toUnicode(components.host);
            } catch (e2) {
              components.error = components.error || "Host's domain name can not be converted to " + (!options.iri ? "ASCII" : "Unicode") + " via punycode: " + e2;
            }
          }
        }
        _normalizeComponentEncoding(components, protocol);
        if (options.reference !== "suffix" && components.scheme) {
          uriTokens.push(components.scheme);
          uriTokens.push(":");
        }
        var authority = _recomposeAuthority(components, options);
        if (authority !== void 0) {
          if (options.reference !== "suffix") {
            uriTokens.push("//");
          }
          uriTokens.push(authority);
          if (components.path && components.path.charAt(0) !== "/") {
            uriTokens.push("/");
          }
        }
        if (components.path !== void 0) {
          var s2 = components.path;
          if (!options.absolutePath && (!schemeHandler || !schemeHandler.absolutePath)) {
            s2 = removeDotSegments(s2);
          }
          if (authority === void 0) {
            s2 = s2.replace(/^\/\//, "/%2F");
          }
          uriTokens.push(s2);
        }
        if (components.query !== void 0) {
          uriTokens.push("?");
          uriTokens.push(components.query);
        }
        if (components.fragment !== void 0) {
          uriTokens.push("#");
          uriTokens.push(components.fragment);
        }
        return uriTokens.join("");
      }
      function resolveComponents(base2, relative) {
        var options = arguments.length > 2 && arguments[2] !== void 0 ? arguments[2] : {};
        var skipNormalization = arguments[3];
        var target = {};
        if (!skipNormalization) {
          base2 = parse(serialize(base2, options), options);
          relative = parse(serialize(relative, options), options);
        }
        options = options || {};
        if (!options.tolerant && relative.scheme) {
          target.scheme = relative.scheme;
          target.userinfo = relative.userinfo;
          target.host = relative.host;
          target.port = relative.port;
          target.path = removeDotSegments(relative.path || "");
          target.query = relative.query;
        } else {
          if (relative.userinfo !== void 0 || relative.host !== void 0 || relative.port !== void 0) {
            target.userinfo = relative.userinfo;
            target.host = relative.host;
            target.port = relative.port;
            target.path = removeDotSegments(relative.path || "");
            target.query = relative.query;
          } else {
            if (!relative.path) {
              target.path = base2.path;
              if (relative.query !== void 0) {
                target.query = relative.query;
              } else {
                target.query = base2.query;
              }
            } else {
              if (relative.path.charAt(0) === "/") {
                target.path = removeDotSegments(relative.path);
              } else {
                if ((base2.userinfo !== void 0 || base2.host !== void 0 || base2.port !== void 0) && !base2.path) {
                  target.path = "/" + relative.path;
                } else if (!base2.path) {
                  target.path = relative.path;
                } else {
                  target.path = base2.path.slice(0, base2.path.lastIndexOf("/") + 1) + relative.path;
                }
                target.path = removeDotSegments(target.path);
              }
              target.query = relative.query;
            }
            target.userinfo = base2.userinfo;
            target.host = base2.host;
            target.port = base2.port;
          }
          target.scheme = base2.scheme;
        }
        target.fragment = relative.fragment;
        return target;
      }
      function resolve(baseURI, relativeURI, options) {
        var schemelessOptions = assign({ scheme: "null" }, options);
        return serialize(resolveComponents(parse(baseURI, schemelessOptions), parse(relativeURI, schemelessOptions), schemelessOptions, true), schemelessOptions);
      }
      function normalize(uri, options) {
        if (typeof uri === "string") {
          uri = serialize(parse(uri, options), options);
        } else if (typeOf(uri) === "object") {
          uri = parse(serialize(uri, options), options);
        }
        return uri;
      }
      function equal(uriA, uriB, options) {
        if (typeof uriA === "string") {
          uriA = serialize(parse(uriA, options), options);
        } else if (typeOf(uriA) === "object") {
          uriA = serialize(uriA, options);
        }
        if (typeof uriB === "string") {
          uriB = serialize(parse(uriB, options), options);
        } else if (typeOf(uriB) === "object") {
          uriB = serialize(uriB, options);
        }
        return uriA === uriB;
      }
      function escapeComponent(str, options) {
        return str && str.toString().replace(!options || !options.iri ? URI_PROTOCOL.ESCAPE : IRI_PROTOCOL.ESCAPE, pctEncChar);
      }
      function unescapeComponent(str, options) {
        return str && str.toString().replace(!options || !options.iri ? URI_PROTOCOL.PCT_ENCODED : IRI_PROTOCOL.PCT_ENCODED, pctDecChars);
      }
      var handler = {
        scheme: "http",
        domainHost: true,
        parse: function parse2(components, options) {
          if (!components.host) {
            components.error = components.error || "HTTP URIs must have a host.";
          }
          return components;
        },
        serialize: function serialize2(components, options) {
          var secure = String(components.scheme).toLowerCase() === "https";
          if (components.port === (secure ? 443 : 80) || components.port === "") {
            components.port = void 0;
          }
          if (!components.path) {
            components.path = "/";
          }
          return components;
        }
      };
      var handler$1 = {
        scheme: "https",
        domainHost: handler.domainHost,
        parse: handler.parse,
        serialize: handler.serialize
      };
      function isSecure(wsComponents) {
        return typeof wsComponents.secure === "boolean" ? wsComponents.secure : String(wsComponents.scheme).toLowerCase() === "wss";
      }
      var handler$2 = {
        scheme: "ws",
        domainHost: true,
        parse: function parse2(components, options) {
          var wsComponents = components;
          wsComponents.secure = isSecure(wsComponents);
          wsComponents.resourceName = (wsComponents.path || "/") + (wsComponents.query ? "?" + wsComponents.query : "");
          wsComponents.path = void 0;
          wsComponents.query = void 0;
          return wsComponents;
        },
        serialize: function serialize2(wsComponents, options) {
          if (wsComponents.port === (isSecure(wsComponents) ? 443 : 80) || wsComponents.port === "") {
            wsComponents.port = void 0;
          }
          if (typeof wsComponents.secure === "boolean") {
            wsComponents.scheme = wsComponents.secure ? "wss" : "ws";
            wsComponents.secure = void 0;
          }
          if (wsComponents.resourceName) {
            var _wsComponents$resourc = wsComponents.resourceName.split("?"), _wsComponents$resourc2 = slicedToArray(_wsComponents$resourc, 2), path = _wsComponents$resourc2[0], query = _wsComponents$resourc2[1];
            wsComponents.path = path && path !== "/" ? path : void 0;
            wsComponents.query = query;
            wsComponents.resourceName = void 0;
          }
          wsComponents.fragment = void 0;
          return wsComponents;
        }
      };
      var handler$3 = {
        scheme: "wss",
        domainHost: handler$2.domainHost,
        parse: handler$2.parse,
        serialize: handler$2.serialize
      };
      var O2 = {};
      var UNRESERVED$$ = "[A-Za-z0-9\\-\\.\\_\\~\\xA0-\\u200D\\u2010-\\u2029\\u202F-\\uD7FF\\uF900-\\uFDCF\\uFDF0-\\uFFEF]";
      var HEXDIG$$ = "[0-9A-Fa-f]";
      var PCT_ENCODED$ = subexp(subexp("%[EFef]" + HEXDIG$$ + "%" + HEXDIG$$ + HEXDIG$$ + "%" + HEXDIG$$ + HEXDIG$$) + "|" + subexp("%[89A-Fa-f]" + HEXDIG$$ + "%" + HEXDIG$$ + HEXDIG$$) + "|" + subexp("%" + HEXDIG$$ + HEXDIG$$));
      var ATEXT$$ = "[A-Za-z0-9\\!\\$\\%\\'\\*\\+\\-\\^\\_\\`\\{\\|\\}\\~]";
      var QTEXT$$ = "[\\!\\$\\%\\'\\(\\)\\*\\+\\,\\-\\.0-9\\<\\>A-Z\\x5E-\\x7E]";
      var VCHAR$$ = merge(QTEXT$$, '[\\"\\\\]');
      var SOME_DELIMS$$ = "[\\!\\$\\'\\(\\)\\*\\+\\,\\;\\:\\@]";
      var UNRESERVED = new RegExp(UNRESERVED$$, "g");
      var PCT_ENCODED = new RegExp(PCT_ENCODED$, "g");
      var NOT_LOCAL_PART = new RegExp(merge("[^]", ATEXT$$, "[\\.]", '[\\"]', VCHAR$$), "g");
      var NOT_HFNAME = new RegExp(merge("[^]", UNRESERVED$$, SOME_DELIMS$$), "g");
      var NOT_HFVALUE = NOT_HFNAME;
      function decodeUnreserved(str) {
        var decStr = pctDecChars(str);
        return !decStr.match(UNRESERVED) ? str : decStr;
      }
      var handler$4 = {
        scheme: "mailto",
        parse: function parse$$1(components, options) {
          var mailtoComponents = components;
          var to = mailtoComponents.to = mailtoComponents.path ? mailtoComponents.path.split(",") : [];
          mailtoComponents.path = void 0;
          if (mailtoComponents.query) {
            var unknownHeaders = false;
            var headers = {};
            var hfields = mailtoComponents.query.split("&");
            for (var x2 = 0, xl = hfields.length; x2 < xl; ++x2) {
              var hfield = hfields[x2].split("=");
              switch (hfield[0]) {
                case "to":
                  var toAddrs = hfield[1].split(",");
                  for (var _x = 0, _xl = toAddrs.length; _x < _xl; ++_x) {
                    to.push(toAddrs[_x]);
                  }
                  break;
                case "subject":
                  mailtoComponents.subject = unescapeComponent(hfield[1], options);
                  break;
                case "body":
                  mailtoComponents.body = unescapeComponent(hfield[1], options);
                  break;
                default:
                  unknownHeaders = true;
                  headers[unescapeComponent(hfield[0], options)] = unescapeComponent(hfield[1], options);
                  break;
              }
            }
            if (unknownHeaders) mailtoComponents.headers = headers;
          }
          mailtoComponents.query = void 0;
          for (var _x2 = 0, _xl2 = to.length; _x2 < _xl2; ++_x2) {
            var addr = to[_x2].split("@");
            addr[0] = unescapeComponent(addr[0]);
            if (!options.unicodeSupport) {
              try {
                addr[1] = punycode.toASCII(unescapeComponent(addr[1], options).toLowerCase());
              } catch (e2) {
                mailtoComponents.error = mailtoComponents.error || "Email address's domain name can not be converted to ASCII via punycode: " + e2;
              }
            } else {
              addr[1] = unescapeComponent(addr[1], options).toLowerCase();
            }
            to[_x2] = addr.join("@");
          }
          return mailtoComponents;
        },
        serialize: function serialize$$1(mailtoComponents, options) {
          var components = mailtoComponents;
          var to = toArray(mailtoComponents.to);
          if (to) {
            for (var x2 = 0, xl = to.length; x2 < xl; ++x2) {
              var toAddr = String(to[x2]);
              var atIdx = toAddr.lastIndexOf("@");
              var localPart = toAddr.slice(0, atIdx).replace(PCT_ENCODED, decodeUnreserved).replace(PCT_ENCODED, toUpperCase).replace(NOT_LOCAL_PART, pctEncChar);
              var domain = toAddr.slice(atIdx + 1);
              try {
                domain = !options.iri ? punycode.toASCII(unescapeComponent(domain, options).toLowerCase()) : punycode.toUnicode(domain);
              } catch (e2) {
                components.error = components.error || "Email address's domain name can not be converted to " + (!options.iri ? "ASCII" : "Unicode") + " via punycode: " + e2;
              }
              to[x2] = localPart + "@" + domain;
            }
            components.path = to.join(",");
          }
          var headers = mailtoComponents.headers = mailtoComponents.headers || {};
          if (mailtoComponents.subject) headers["subject"] = mailtoComponents.subject;
          if (mailtoComponents.body) headers["body"] = mailtoComponents.body;
          var fields = [];
          for (var name in headers) {
            if (headers[name] !== O2[name]) {
              fields.push(name.replace(PCT_ENCODED, decodeUnreserved).replace(PCT_ENCODED, toUpperCase).replace(NOT_HFNAME, pctEncChar) + "=" + headers[name].replace(PCT_ENCODED, decodeUnreserved).replace(PCT_ENCODED, toUpperCase).replace(NOT_HFVALUE, pctEncChar));
            }
          }
          if (fields.length) {
            components.query = fields.join("&");
          }
          return components;
        }
      };
      var URN_PARSE = /^([^\:]+)\:(.*)/;
      var handler$5 = {
        scheme: "urn",
        parse: function parse$$1(components, options) {
          var matches = components.path && components.path.match(URN_PARSE);
          var urnComponents = components;
          if (matches) {
            var scheme = options.scheme || urnComponents.scheme || "urn";
            var nid = matches[1].toLowerCase();
            var nss = matches[2];
            var urnScheme = scheme + ":" + (options.nid || nid);
            var schemeHandler = SCHEMES[urnScheme];
            urnComponents.nid = nid;
            urnComponents.nss = nss;
            urnComponents.path = void 0;
            if (schemeHandler) {
              urnComponents = schemeHandler.parse(urnComponents, options);
            }
          } else {
            urnComponents.error = urnComponents.error || "URN can not be parsed.";
          }
          return urnComponents;
        },
        serialize: function serialize$$1(urnComponents, options) {
          var scheme = options.scheme || urnComponents.scheme || "urn";
          var nid = urnComponents.nid;
          var urnScheme = scheme + ":" + (options.nid || nid);
          var schemeHandler = SCHEMES[urnScheme];
          if (schemeHandler) {
            urnComponents = schemeHandler.serialize(urnComponents, options);
          }
          var uriComponents = urnComponents;
          var nss = urnComponents.nss;
          uriComponents.path = (nid || options.nid) + ":" + nss;
          return uriComponents;
        }
      };
      var UUID = /^[0-9A-Fa-f]{8}(?:\-[0-9A-Fa-f]{4}){3}\-[0-9A-Fa-f]{12}$/;
      var handler$6 = {
        scheme: "urn:uuid",
        parse: function parse2(urnComponents, options) {
          var uuidComponents = urnComponents;
          uuidComponents.uuid = uuidComponents.nss;
          uuidComponents.nss = void 0;
          if (!options.tolerant && (!uuidComponents.uuid || !uuidComponents.uuid.match(UUID))) {
            uuidComponents.error = uuidComponents.error || "UUID is not valid.";
          }
          return uuidComponents;
        },
        serialize: function serialize2(uuidComponents, options) {
          var urnComponents = uuidComponents;
          urnComponents.nss = (uuidComponents.uuid || "").toLowerCase();
          return urnComponents;
        }
      };
      SCHEMES[handler.scheme] = handler;
      SCHEMES[handler$1.scheme] = handler$1;
      SCHEMES[handler$2.scheme] = handler$2;
      SCHEMES[handler$3.scheme] = handler$3;
      SCHEMES[handler$4.scheme] = handler$4;
      SCHEMES[handler$5.scheme] = handler$5;
      SCHEMES[handler$6.scheme] = handler$6;
      exports2.SCHEMES = SCHEMES;
      exports2.pctEncChar = pctEncChar;
      exports2.pctDecChars = pctDecChars;
      exports2.parse = parse;
      exports2.removeDotSegments = removeDotSegments;
      exports2.serialize = serialize;
      exports2.resolveComponents = resolveComponents;
      exports2.resolve = resolve;
      exports2.normalize = normalize;
      exports2.equal = equal;
      exports2.escapeComponent = escapeComponent;
      exports2.unescapeComponent = unescapeComponent;
      Object.defineProperty(exports2, "__esModule", { value: true });
    }));
  })(uri_all$1, uri_all$1.exports);
  return uri_all$1.exports;
}
var fastDeepEqual;
var hasRequiredFastDeepEqual;
function requireFastDeepEqual() {
  if (hasRequiredFastDeepEqual) return fastDeepEqual;
  hasRequiredFastDeepEqual = 1;
  fastDeepEqual = function equal(a2, b2) {
    if (a2 === b2) return true;
    if (a2 && b2 && typeof a2 == "object" && typeof b2 == "object") {
      if (a2.constructor !== b2.constructor) return false;
      var length, i2, keys;
      if (Array.isArray(a2)) {
        length = a2.length;
        if (length != b2.length) return false;
        for (i2 = length; i2-- !== 0; )
          if (!equal(a2[i2], b2[i2])) return false;
        return true;
      }
      if (a2.constructor === RegExp) return a2.source === b2.source && a2.flags === b2.flags;
      if (a2.valueOf !== Object.prototype.valueOf) return a2.valueOf() === b2.valueOf();
      if (a2.toString !== Object.prototype.toString) return a2.toString() === b2.toString();
      keys = Object.keys(a2);
      length = keys.length;
      if (length !== Object.keys(b2).length) return false;
      for (i2 = length; i2-- !== 0; )
        if (!Object.prototype.hasOwnProperty.call(b2, keys[i2])) return false;
      for (i2 = length; i2-- !== 0; ) {
        var key = keys[i2];
        if (!equal(a2[key], b2[key])) return false;
      }
      return true;
    }
    return a2 !== a2 && b2 !== b2;
  };
  return fastDeepEqual;
}
var ucs2length;
var hasRequiredUcs2length;
function requireUcs2length() {
  if (hasRequiredUcs2length) return ucs2length;
  hasRequiredUcs2length = 1;
  ucs2length = function ucs2length2(str) {
    var length = 0, len = str.length, pos = 0, value;
    while (pos < len) {
      length++;
      value = str.charCodeAt(pos++);
      if (value >= 55296 && value <= 56319 && pos < len) {
        value = str.charCodeAt(pos);
        if ((value & 64512) == 56320) pos++;
      }
    }
    return length;
  };
  return ucs2length;
}
var util;
var hasRequiredUtil;
function requireUtil() {
  if (hasRequiredUtil) return util;
  hasRequiredUtil = 1;
  util = {
    copy,
    checkDataType,
    checkDataTypes,
    coerceToTypes,
    toHash,
    getProperty,
    escapeQuotes,
    equal: requireFastDeepEqual(),
    ucs2length: requireUcs2length(),
    varOccurences,
    varReplace,
    schemaHasRules,
    schemaHasRulesExcept,
    schemaUnknownRules,
    toQuotedString,
    getPathExpr,
    getPath,
    getData,
    unescapeFragment,
    unescapeJsonPointer,
    escapeFragment,
    escapeJsonPointer
  };
  function copy(o2, to) {
    to = to || {};
    for (var key in o2) to[key] = o2[key];
    return to;
  }
  function checkDataType(dataType, data2, strictNumbers, negate) {
    var EQUAL = negate ? " !== " : " === ", AND = negate ? " || " : " && ", OK2 = negate ? "!" : "", NOT = negate ? "" : "!";
    switch (dataType) {
      case "null":
        return data2 + EQUAL + "null";
      case "array":
        return OK2 + "Array.isArray(" + data2 + ")";
      case "object":
        return "(" + OK2 + data2 + AND + "typeof " + data2 + EQUAL + '"object"' + AND + NOT + "Array.isArray(" + data2 + "))";
      case "integer":
        return "(typeof " + data2 + EQUAL + '"number"' + AND + NOT + "(" + data2 + " % 1)" + AND + data2 + EQUAL + data2 + (strictNumbers ? AND + OK2 + "isFinite(" + data2 + ")" : "") + ")";
      case "number":
        return "(typeof " + data2 + EQUAL + '"' + dataType + '"' + (strictNumbers ? AND + OK2 + "isFinite(" + data2 + ")" : "") + ")";
      default:
        return "typeof " + data2 + EQUAL + '"' + dataType + '"';
    }
  }
  function checkDataTypes(dataTypes, data2, strictNumbers) {
    switch (dataTypes.length) {
      case 1:
        return checkDataType(dataTypes[0], data2, strictNumbers, true);
      default:
        var code = "";
        var types = toHash(dataTypes);
        if (types.array && types.object) {
          code = types.null ? "(" : "(!" + data2 + " || ";
          code += "typeof " + data2 + ' !== "object")';
          delete types.null;
          delete types.array;
          delete types.object;
        }
        if (types.number) delete types.integer;
        for (var t2 in types)
          code += (code ? " && " : "") + checkDataType(t2, data2, strictNumbers, true);
        return code;
    }
  }
  var COERCE_TO_TYPES = toHash(["string", "number", "integer", "boolean", "null"]);
  function coerceToTypes(optionCoerceTypes, dataTypes) {
    if (Array.isArray(dataTypes)) {
      var types = [];
      for (var i2 = 0; i2 < dataTypes.length; i2++) {
        var t2 = dataTypes[i2];
        if (COERCE_TO_TYPES[t2]) types[types.length] = t2;
        else if (optionCoerceTypes === "array" && t2 === "array") types[types.length] = t2;
      }
      if (types.length) return types;
    } else if (COERCE_TO_TYPES[dataTypes]) {
      return [dataTypes];
    } else if (optionCoerceTypes === "array" && dataTypes === "array") {
      return ["array"];
    }
  }
  function toHash(arr) {
    var hash = {};
    for (var i2 = 0; i2 < arr.length; i2++) hash[arr[i2]] = true;
    return hash;
  }
  var IDENTIFIER = /^[a-z$_][a-z$_0-9]*$/i;
  var SINGLE_QUOTE = /'|\\/g;
  function getProperty(key) {
    return typeof key == "number" ? "[" + key + "]" : IDENTIFIER.test(key) ? "." + key : "['" + escapeQuotes(key) + "']";
  }
  function escapeQuotes(str) {
    return str.replace(SINGLE_QUOTE, "\\$&").replace(/\n/g, "\\n").replace(/\r/g, "\\r").replace(/\f/g, "\\f").replace(/\t/g, "\\t");
  }
  function varOccurences(str, dataVar) {
    dataVar += "[^0-9]";
    var matches = str.match(new RegExp(dataVar, "g"));
    return matches ? matches.length : 0;
  }
  function varReplace(str, dataVar, expr) {
    dataVar += "([^0-9])";
    expr = expr.replace(/\$/g, "$$$$");
    return str.replace(new RegExp(dataVar, "g"), expr + "$1");
  }
  function schemaHasRules(schema, rules2) {
    if (typeof schema == "boolean") return !schema;
    for (var key in schema) if (rules2[key]) return true;
  }
  function schemaHasRulesExcept(schema, rules2, exceptKeyword) {
    if (typeof schema == "boolean") return !schema && exceptKeyword != "not";
    for (var key in schema) if (key != exceptKeyword && rules2[key]) return true;
  }
  function schemaUnknownRules(schema, rules2) {
    if (typeof schema == "boolean") return;
    for (var key in schema) if (!rules2[key]) return key;
  }
  function toQuotedString(str) {
    return "'" + escapeQuotes(str) + "'";
  }
  function getPathExpr(currentPath, expr, jsonPointers, isNumber) {
    var path = jsonPointers ? "'/' + " + expr + (isNumber ? "" : ".replace(/~/g, '~0').replace(/\\//g, '~1')") : isNumber ? "'[' + " + expr + " + ']'" : "'[\\'' + " + expr + " + '\\']'";
    return joinPaths(currentPath, path);
  }
  function getPath(currentPath, prop, jsonPointers) {
    var path = jsonPointers ? toQuotedString("/" + escapeJsonPointer(prop)) : toQuotedString(getProperty(prop));
    return joinPaths(currentPath, path);
  }
  var JSON_POINTER = /^\/(?:[^~]|~0|~1)*$/;
  var RELATIVE_JSON_POINTER = /^([0-9]+)(#|\/(?:[^~]|~0|~1)*)?$/;
  function getData($data, lvl, paths) {
    var up, jsonPointer, data2, matches;
    if ($data === "") return "rootData";
    if ($data[0] == "/") {
      if (!JSON_POINTER.test($data)) throw new Error("Invalid JSON-pointer: " + $data);
      jsonPointer = $data;
      data2 = "rootData";
    } else {
      matches = $data.match(RELATIVE_JSON_POINTER);
      if (!matches) throw new Error("Invalid JSON-pointer: " + $data);
      up = +matches[1];
      jsonPointer = matches[2];
      if (jsonPointer == "#") {
        if (up >= lvl) throw new Error("Cannot access property/index " + up + " levels up, current level is " + lvl);
        return paths[lvl - up];
      }
      if (up > lvl) throw new Error("Cannot access data " + up + " levels up, current level is " + lvl);
      data2 = "data" + (lvl - up || "");
      if (!jsonPointer) return data2;
    }
    var expr = data2;
    var segments = jsonPointer.split("/");
    for (var i2 = 0; i2 < segments.length; i2++) {
      var segment = segments[i2];
      if (segment) {
        data2 += getProperty(unescapeJsonPointer(segment));
        expr += " && " + data2;
      }
    }
    return expr;
  }
  function joinPaths(a2, b2) {
    if (a2 == '""') return b2;
    return (a2 + " + " + b2).replace(/([^\\])' \+ '/g, "$1");
  }
  function unescapeFragment(str) {
    return unescapeJsonPointer(decodeURIComponent(str));
  }
  function escapeFragment(str) {
    return encodeURIComponent(escapeJsonPointer(str));
  }
  function escapeJsonPointer(str) {
    return str.replace(/~/g, "~0").replace(/\//g, "~1");
  }
  function unescapeJsonPointer(str) {
    return str.replace(/~1/g, "/").replace(/~0/g, "~");
  }
  return util;
}
var schema_obj;
var hasRequiredSchema_obj;
function requireSchema_obj() {
  if (hasRequiredSchema_obj) return schema_obj;
  hasRequiredSchema_obj = 1;
  var util2 = requireUtil();
  schema_obj = SchemaObject;
  function SchemaObject(obj) {
    util2.copy(obj, this);
  }
  return schema_obj;
}
var jsonSchemaTraverse = { exports: {} };
var hasRequiredJsonSchemaTraverse;
function requireJsonSchemaTraverse() {
  if (hasRequiredJsonSchemaTraverse) return jsonSchemaTraverse.exports;
  hasRequiredJsonSchemaTraverse = 1;
  var traverse = jsonSchemaTraverse.exports = function(schema, opts, cb) {
    if (typeof opts == "function") {
      cb = opts;
      opts = {};
    }
    cb = opts.cb || cb;
    var pre = typeof cb == "function" ? cb : cb.pre || function() {
    };
    var post = cb.post || function() {
    };
    _traverse(opts, pre, post, schema, "", schema);
  };
  traverse.keywords = {
    additionalItems: true,
    items: true,
    contains: true,
    additionalProperties: true,
    propertyNames: true,
    not: true
  };
  traverse.arrayKeywords = {
    items: true,
    allOf: true,
    anyOf: true,
    oneOf: true
  };
  traverse.propsKeywords = {
    definitions: true,
    properties: true,
    patternProperties: true,
    dependencies: true
  };
  traverse.skipKeywords = {
    default: true,
    enum: true,
    const: true,
    required: true,
    maximum: true,
    minimum: true,
    exclusiveMaximum: true,
    exclusiveMinimum: true,
    multipleOf: true,
    maxLength: true,
    minLength: true,
    pattern: true,
    format: true,
    maxItems: true,
    minItems: true,
    uniqueItems: true,
    maxProperties: true,
    minProperties: true
  };
  function _traverse(opts, pre, post, schema, jsonPtr, rootSchema, parentJsonPtr, parentKeyword, parentSchema, keyIndex) {
    if (schema && typeof schema == "object" && !Array.isArray(schema)) {
      pre(schema, jsonPtr, rootSchema, parentJsonPtr, parentKeyword, parentSchema, keyIndex);
      for (var key in schema) {
        var sch = schema[key];
        if (Array.isArray(sch)) {
          if (key in traverse.arrayKeywords) {
            for (var i2 = 0; i2 < sch.length; i2++)
              _traverse(opts, pre, post, sch[i2], jsonPtr + "/" + key + "/" + i2, rootSchema, jsonPtr, key, schema, i2);
          }
        } else if (key in traverse.propsKeywords) {
          if (sch && typeof sch == "object") {
            for (var prop in sch)
              _traverse(opts, pre, post, sch[prop], jsonPtr + "/" + key + "/" + escapeJsonPtr(prop), rootSchema, jsonPtr, key, schema, prop);
          }
        } else if (key in traverse.keywords || opts.allKeys && !(key in traverse.skipKeywords)) {
          _traverse(opts, pre, post, sch, jsonPtr + "/" + key, rootSchema, jsonPtr, key, schema);
        }
      }
      post(schema, jsonPtr, rootSchema, parentJsonPtr, parentKeyword, parentSchema, keyIndex);
    }
  }
  function escapeJsonPtr(str) {
    return str.replace(/~/g, "~0").replace(/\//g, "~1");
  }
  return jsonSchemaTraverse.exports;
}
var resolve_1;
var hasRequiredResolve;
function requireResolve() {
  if (hasRequiredResolve) return resolve_1;
  hasRequiredResolve = 1;
  var URI = requireUri_all(), equal = requireFastDeepEqual(), util2 = requireUtil(), SchemaObject = requireSchema_obj(), traverse = requireJsonSchemaTraverse();
  resolve_1 = resolve;
  resolve.normalizeId = normalizeId;
  resolve.fullPath = getFullPath;
  resolve.url = resolveUrl;
  resolve.ids = resolveIds;
  resolve.inlineRef = inlineRef;
  resolve.schema = resolveSchema;
  function resolve(compile, root, ref2) {
    var refVal = this._refs[ref2];
    if (typeof refVal == "string") {
      if (this._refs[refVal]) refVal = this._refs[refVal];
      else return resolve.call(this, compile, root, refVal);
    }
    refVal = refVal || this._schemas[ref2];
    if (refVal instanceof SchemaObject) {
      return inlineRef(refVal.schema, this._opts.inlineRefs) ? refVal.schema : refVal.validate || this._compile(refVal);
    }
    var res = resolveSchema.call(this, root, ref2);
    var schema, v2, baseId;
    if (res) {
      schema = res.schema;
      root = res.root;
      baseId = res.baseId;
    }
    if (schema instanceof SchemaObject) {
      v2 = schema.validate || compile.call(this, schema.schema, root, void 0, baseId);
    } else if (schema !== void 0) {
      v2 = inlineRef(schema, this._opts.inlineRefs) ? schema : compile.call(this, schema, root, void 0, baseId);
    }
    return v2;
  }
  function resolveSchema(root, ref2) {
    var p2 = URI.parse(ref2), refPath = _getFullPath(p2), baseId = getFullPath(this._getId(root.schema));
    if (Object.keys(root.schema).length === 0 || refPath !== baseId) {
      var id = normalizeId(refPath);
      var refVal = this._refs[id];
      if (typeof refVal == "string") {
        return resolveRecursive.call(this, root, refVal, p2);
      } else if (refVal instanceof SchemaObject) {
        if (!refVal.validate) this._compile(refVal);
        root = refVal;
      } else {
        refVal = this._schemas[id];
        if (refVal instanceof SchemaObject) {
          if (!refVal.validate) this._compile(refVal);
          if (id == normalizeId(ref2))
            return { schema: refVal, root, baseId };
          root = refVal;
        } else {
          return;
        }
      }
      if (!root.schema) return;
      baseId = getFullPath(this._getId(root.schema));
    }
    return getJsonPointer.call(this, p2, baseId, root.schema, root);
  }
  function resolveRecursive(root, ref2, parsedRef) {
    var res = resolveSchema.call(this, root, ref2);
    if (res) {
      var schema = res.schema;
      var baseId = res.baseId;
      root = res.root;
      var id = this._getId(schema);
      if (id) baseId = resolveUrl(baseId, id);
      return getJsonPointer.call(this, parsedRef, baseId, schema, root);
    }
  }
  var PREVENT_SCOPE_CHANGE = util2.toHash(["properties", "patternProperties", "enum", "dependencies", "definitions"]);
  function getJsonPointer(parsedRef, baseId, schema, root) {
    parsedRef.fragment = parsedRef.fragment || "";
    if (parsedRef.fragment.slice(0, 1) != "/") return;
    var parts = parsedRef.fragment.split("/");
    for (var i2 = 1; i2 < parts.length; i2++) {
      var part = parts[i2];
      if (part) {
        part = util2.unescapeFragment(part);
        schema = schema[part];
        if (schema === void 0) break;
        var id;
        if (!PREVENT_SCOPE_CHANGE[part]) {
          id = this._getId(schema);
          if (id) baseId = resolveUrl(baseId, id);
          if (schema.$ref) {
            var $ref = resolveUrl(baseId, schema.$ref);
            var res = resolveSchema.call(this, root, $ref);
            if (res) {
              schema = res.schema;
              root = res.root;
              baseId = res.baseId;
            }
          }
        }
      }
    }
    if (schema !== void 0 && schema !== root.schema)
      return { schema, root, baseId };
  }
  var SIMPLE_INLINED = util2.toHash([
    "type",
    "format",
    "pattern",
    "maxLength",
    "minLength",
    "maxProperties",
    "minProperties",
    "maxItems",
    "minItems",
    "maximum",
    "minimum",
    "uniqueItems",
    "multipleOf",
    "required",
    "enum"
  ]);
  function inlineRef(schema, limit) {
    if (limit === false) return false;
    if (limit === void 0 || limit === true) return checkNoRef(schema);
    else if (limit) return countKeys(schema) <= limit;
  }
  function checkNoRef(schema) {
    var item;
    if (Array.isArray(schema)) {
      for (var i2 = 0; i2 < schema.length; i2++) {
        item = schema[i2];
        if (typeof item == "object" && !checkNoRef(item)) return false;
      }
    } else {
      for (var key in schema) {
        if (key == "$ref") return false;
        item = schema[key];
        if (typeof item == "object" && !checkNoRef(item)) return false;
      }
    }
    return true;
  }
  function countKeys(schema) {
    var count = 0, item;
    if (Array.isArray(schema)) {
      for (var i2 = 0; i2 < schema.length; i2++) {
        item = schema[i2];
        if (typeof item == "object") count += countKeys(item);
        if (count == Infinity) return Infinity;
      }
    } else {
      for (var key in schema) {
        if (key == "$ref") return Infinity;
        if (SIMPLE_INLINED[key]) {
          count++;
        } else {
          item = schema[key];
          if (typeof item == "object") count += countKeys(item) + 1;
          if (count == Infinity) return Infinity;
        }
      }
    }
    return count;
  }
  function getFullPath(id, normalize) {
    if (normalize !== false) id = normalizeId(id);
    var p2 = URI.parse(id);
    return _getFullPath(p2);
  }
  function _getFullPath(p2) {
    return URI.serialize(p2).split("#")[0] + "#";
  }
  var TRAILING_SLASH_HASH = /#\/?$/;
  function normalizeId(id) {
    return id ? id.replace(TRAILING_SLASH_HASH, "") : "";
  }
  function resolveUrl(baseId, id) {
    id = normalizeId(id);
    return URI.resolve(baseId, id);
  }
  function resolveIds(schema) {
    var schemaId = normalizeId(this._getId(schema));
    var baseIds = { "": schemaId };
    var fullPaths = { "": getFullPath(schemaId, false) };
    var localRefs = {};
    var self = this;
    traverse(schema, { allKeys: true }, function(sch, jsonPtr, rootSchema, parentJsonPtr, parentKeyword, parentSchema, keyIndex) {
      if (jsonPtr === "") return;
      var id = self._getId(sch);
      var baseId = baseIds[parentJsonPtr];
      var fullPath = fullPaths[parentJsonPtr] + "/" + parentKeyword;
      if (keyIndex !== void 0)
        fullPath += "/" + (typeof keyIndex == "number" ? keyIndex : util2.escapeFragment(keyIndex));
      if (typeof id == "string") {
        id = baseId = normalizeId(baseId ? URI.resolve(baseId, id) : id);
        var refVal = self._refs[id];
        if (typeof refVal == "string") refVal = self._refs[refVal];
        if (refVal && refVal.schema) {
          if (!equal(sch, refVal.schema))
            throw new Error('id "' + id + '" resolves to more than one schema');
        } else if (id != normalizeId(fullPath)) {
          if (id[0] == "#") {
            if (localRefs[id] && !equal(sch, localRefs[id]))
              throw new Error('id "' + id + '" resolves to more than one schema');
            localRefs[id] = sch;
          } else {
            self._refs[id] = fullPath;
          }
        }
      }
      baseIds[jsonPtr] = baseId;
      fullPaths[jsonPtr] = fullPath;
    });
    return localRefs;
  }
  return resolve_1;
}
var error_classes;
var hasRequiredError_classes;
function requireError_classes() {
  if (hasRequiredError_classes) return error_classes;
  hasRequiredError_classes = 1;
  var resolve = requireResolve();
  error_classes = {
    Validation: errorSubclass(ValidationError),
    MissingRef: errorSubclass(MissingRefError)
  };
  function ValidationError(errors) {
    this.message = "validation failed";
    this.errors = errors;
    this.ajv = this.validation = true;
  }
  MissingRefError.message = function(baseId, ref2) {
    return "can't resolve reference " + ref2 + " from id " + baseId;
  };
  function MissingRefError(baseId, ref2, message) {
    this.message = message || MissingRefError.message(baseId, ref2);
    this.missingRef = resolve.url(baseId, ref2);
    this.missingSchema = resolve.normalizeId(resolve.fullPath(this.missingRef));
  }
  function errorSubclass(Subclass) {
    Subclass.prototype = Object.create(Error.prototype);
    Subclass.prototype.constructor = Subclass;
    return Subclass;
  }
  return error_classes;
}
var fastJsonStableStringify;
var hasRequiredFastJsonStableStringify;
function requireFastJsonStableStringify() {
  if (hasRequiredFastJsonStableStringify) return fastJsonStableStringify;
  hasRequiredFastJsonStableStringify = 1;
  fastJsonStableStringify = function(data2, opts) {
    if (!opts) opts = {};
    if (typeof opts === "function") opts = { cmp: opts };
    var cycles = typeof opts.cycles === "boolean" ? opts.cycles : false;
    var cmp = opts.cmp && /* @__PURE__ */ (function(f2) {
      return function(node) {
        return function(a2, b2) {
          var aobj = { key: a2, value: node[a2] };
          var bobj = { key: b2, value: node[b2] };
          return f2(aobj, bobj);
        };
      };
    })(opts.cmp);
    var seen = [];
    return (function stringify(node) {
      if (node && node.toJSON && typeof node.toJSON === "function") {
        node = node.toJSON();
      }
      if (node === void 0) return;
      if (typeof node == "number") return isFinite(node) ? "" + node : "null";
      if (typeof node !== "object") return JSON.stringify(node);
      var i2, out;
      if (Array.isArray(node)) {
        out = "[";
        for (i2 = 0; i2 < node.length; i2++) {
          if (i2) out += ",";
          out += stringify(node[i2]) || "null";
        }
        return out + "]";
      }
      if (node === null) return "null";
      if (seen.indexOf(node) !== -1) {
        if (cycles) return JSON.stringify("__cycle__");
        throw new TypeError("Converting circular structure to JSON");
      }
      var seenIndex = seen.push(node) - 1;
      var keys = Object.keys(node).sort(cmp && cmp(node));
      out = "";
      for (i2 = 0; i2 < keys.length; i2++) {
        var key = keys[i2];
        var value = stringify(node[key]);
        if (!value) continue;
        if (out) out += ",";
        out += JSON.stringify(key) + ":" + value;
      }
      seen.splice(seenIndex, 1);
      return "{" + out + "}";
    })(data2);
  };
  return fastJsonStableStringify;
}
var validate;
var hasRequiredValidate;
function requireValidate() {
  if (hasRequiredValidate) return validate;
  hasRequiredValidate = 1;
  validate = function generate_validate(it2, $keyword, $ruleType) {
    var out = "";
    var $async = it2.schema.$async === true, $refKeywords = it2.util.schemaHasRulesExcept(it2.schema, it2.RULES.all, "$ref"), $id2 = it2.self._getId(it2.schema);
    if (it2.opts.strictKeywords) {
      var $unknownKwd = it2.util.schemaUnknownRules(it2.schema, it2.RULES.keywords);
      if ($unknownKwd) {
        var $keywordsMsg = "unknown keyword: " + $unknownKwd;
        if (it2.opts.strictKeywords === "log") it2.logger.warn($keywordsMsg);
        else throw new Error($keywordsMsg);
      }
    }
    if (it2.isTop) {
      out += " var validate = ";
      if ($async) {
        it2.async = true;
        out += "async ";
      }
      out += "function(data, dataPath, parentData, parentDataProperty, rootData) { 'use strict'; ";
      if ($id2 && (it2.opts.sourceCode || it2.opts.processCode)) {
        out += " " + ("/*# sourceURL=" + $id2 + " */") + " ";
      }
    }
    if (typeof it2.schema == "boolean" || !($refKeywords || it2.schema.$ref)) {
      var $keyword = "false schema";
      var $lvl = it2.level;
      var $dataLvl = it2.dataLevel;
      var $schema2 = it2.schema[$keyword];
      var $schemaPath = it2.schemaPath + it2.util.getProperty($keyword);
      var $errSchemaPath = it2.errSchemaPath + "/" + $keyword;
      var $breakOnError = !it2.opts.allErrors;
      var $errorKeyword;
      var $data = "data" + ($dataLvl || "");
      var $valid = "valid" + $lvl;
      if (it2.schema === false) {
        if (it2.isTop) {
          $breakOnError = true;
        } else {
          out += " var " + $valid + " = false; ";
        }
        var $$outStack = $$outStack || [];
        $$outStack.push(out);
        out = "";
        if (it2.createErrors !== false) {
          out += " { keyword: '" + ($errorKeyword || "false schema") + "' , dataPath: (dataPath || '') + " + it2.errorPath + " , schemaPath: " + it2.util.toQuotedString($errSchemaPath) + " , params: {} ";
          if (it2.opts.messages !== false) {
            out += " , message: 'boolean schema is false' ";
          }
          if (it2.opts.verbose) {
            out += " , schema: false , parentSchema: validate.schema" + it2.schemaPath + " , data: " + $data + " ";
          }
          out += " } ";
        } else {
          out += " {} ";
        }
        var __err = out;
        out = $$outStack.pop();
        if (!it2.compositeRule && $breakOnError) {
          if (it2.async) {
            out += " throw new ValidationError([" + __err + "]); ";
          } else {
            out += " validate.errors = [" + __err + "]; return false; ";
          }
        } else {
          out += " var err = " + __err + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ";
        }
      } else {
        if (it2.isTop) {
          if ($async) {
            out += " return data; ";
          } else {
            out += " validate.errors = null; return true; ";
          }
        } else {
          out += " var " + $valid + " = true; ";
        }
      }
      if (it2.isTop) {
        out += " }; return validate; ";
      }
      return out;
    }
    if (it2.isTop) {
      var $top = it2.isTop, $lvl = it2.level = 0, $dataLvl = it2.dataLevel = 0, $data = "data";
      it2.rootId = it2.resolve.fullPath(it2.self._getId(it2.root.schema));
      it2.baseId = it2.baseId || it2.rootId;
      delete it2.isTop;
      it2.dataPathArr = [""];
      if (it2.schema.default !== void 0 && it2.opts.useDefaults && it2.opts.strictDefaults) {
        var $defaultMsg = "default is ignored in the schema root";
        if (it2.opts.strictDefaults === "log") it2.logger.warn($defaultMsg);
        else throw new Error($defaultMsg);
      }
      out += " var vErrors = null; ";
      out += " var errors = 0;     ";
      out += " if (rootData === undefined) rootData = data; ";
    } else {
      var $lvl = it2.level, $dataLvl = it2.dataLevel, $data = "data" + ($dataLvl || "");
      if ($id2) it2.baseId = it2.resolve.url(it2.baseId, $id2);
      if ($async && !it2.async) throw new Error("async schema in sync schema");
      out += " var errs_" + $lvl + " = errors;";
    }
    var $valid = "valid" + $lvl, $breakOnError = !it2.opts.allErrors, $closingBraces1 = "", $closingBraces2 = "";
    var $errorKeyword;
    var $typeSchema = it2.schema.type, $typeIsArray = Array.isArray($typeSchema);
    if ($typeSchema && it2.opts.nullable && it2.schema.nullable === true) {
      if ($typeIsArray) {
        if ($typeSchema.indexOf("null") == -1) $typeSchema = $typeSchema.concat("null");
      } else if ($typeSchema != "null") {
        $typeSchema = [$typeSchema, "null"];
        $typeIsArray = true;
      }
    }
    if ($typeIsArray && $typeSchema.length == 1) {
      $typeSchema = $typeSchema[0];
      $typeIsArray = false;
    }
    if (it2.schema.$ref && $refKeywords) {
      if (it2.opts.extendRefs == "fail") {
        throw new Error('$ref: validation keywords used in schema at path "' + it2.errSchemaPath + '" (see option extendRefs)');
      } else if (it2.opts.extendRefs !== true) {
        $refKeywords = false;
        it2.logger.warn('$ref: keywords ignored in schema at path "' + it2.errSchemaPath + '"');
      }
    }
    if (it2.schema.$comment && it2.opts.$comment) {
      out += " " + it2.RULES.all.$comment.code(it2, "$comment");
    }
    if ($typeSchema) {
      if (it2.opts.coerceTypes) {
        var $coerceToTypes = it2.util.coerceToTypes(it2.opts.coerceTypes, $typeSchema);
      }
      var $rulesGroup = it2.RULES.types[$typeSchema];
      if ($coerceToTypes || $typeIsArray || $rulesGroup === true || $rulesGroup && !$shouldUseGroup($rulesGroup)) {
        var $schemaPath = it2.schemaPath + ".type", $errSchemaPath = it2.errSchemaPath + "/type";
        var $schemaPath = it2.schemaPath + ".type", $errSchemaPath = it2.errSchemaPath + "/type", $method = $typeIsArray ? "checkDataTypes" : "checkDataType";
        out += " if (" + it2.util[$method]($typeSchema, $data, it2.opts.strictNumbers, true) + ") { ";
        if ($coerceToTypes) {
          var $dataType = "dataType" + $lvl, $coerced = "coerced" + $lvl;
          out += " var " + $dataType + " = typeof " + $data + "; var " + $coerced + " = undefined; ";
          if (it2.opts.coerceTypes == "array") {
            out += " if (" + $dataType + " == 'object' && Array.isArray(" + $data + ") && " + $data + ".length == 1) { " + $data + " = " + $data + "[0]; " + $dataType + " = typeof " + $data + "; if (" + it2.util.checkDataType(it2.schema.type, $data, it2.opts.strictNumbers) + ") " + $coerced + " = " + $data + "; } ";
          }
          out += " if (" + $coerced + " !== undefined) ; ";
          var arr1 = $coerceToTypes;
          if (arr1) {
            var $type, $i = -1, l1 = arr1.length - 1;
            while ($i < l1) {
              $type = arr1[$i += 1];
              if ($type == "string") {
                out += " else if (" + $dataType + " == 'number' || " + $dataType + " == 'boolean') " + $coerced + " = '' + " + $data + "; else if (" + $data + " === null) " + $coerced + " = ''; ";
              } else if ($type == "number" || $type == "integer") {
                out += " else if (" + $dataType + " == 'boolean' || " + $data + " === null || (" + $dataType + " == 'string' && " + $data + " && " + $data + " == +" + $data + " ";
                if ($type == "integer") {
                  out += " && !(" + $data + " % 1)";
                }
                out += ")) " + $coerced + " = +" + $data + "; ";
              } else if ($type == "boolean") {
                out += " else if (" + $data + " === 'false' || " + $data + " === 0 || " + $data + " === null) " + $coerced + " = false; else if (" + $data + " === 'true' || " + $data + " === 1) " + $coerced + " = true; ";
              } else if ($type == "null") {
                out += " else if (" + $data + " === '' || " + $data + " === 0 || " + $data + " === false) " + $coerced + " = null; ";
              } else if (it2.opts.coerceTypes == "array" && $type == "array") {
                out += " else if (" + $dataType + " == 'string' || " + $dataType + " == 'number' || " + $dataType + " == 'boolean' || " + $data + " == null) " + $coerced + " = [" + $data + "]; ";
              }
            }
          }
          out += " else {   ";
          var $$outStack = $$outStack || [];
          $$outStack.push(out);
          out = "";
          if (it2.createErrors !== false) {
            out += " { keyword: '" + ($errorKeyword || "type") + "' , dataPath: (dataPath || '') + " + it2.errorPath + " , schemaPath: " + it2.util.toQuotedString($errSchemaPath) + " , params: { type: '";
            if ($typeIsArray) {
              out += "" + $typeSchema.join(",");
            } else {
              out += "" + $typeSchema;
            }
            out += "' } ";
            if (it2.opts.messages !== false) {
              out += " , message: 'should be ";
              if ($typeIsArray) {
                out += "" + $typeSchema.join(",");
              } else {
                out += "" + $typeSchema;
              }
              out += "' ";
            }
            if (it2.opts.verbose) {
              out += " , schema: validate.schema" + $schemaPath + " , parentSchema: validate.schema" + it2.schemaPath + " , data: " + $data + " ";
            }
            out += " } ";
          } else {
            out += " {} ";
          }
          var __err = out;
          out = $$outStack.pop();
          if (!it2.compositeRule && $breakOnError) {
            if (it2.async) {
              out += " throw new ValidationError([" + __err + "]); ";
            } else {
              out += " validate.errors = [" + __err + "]; return false; ";
            }
          } else {
            out += " var err = " + __err + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ";
          }
          out += " } if (" + $coerced + " !== undefined) {  ";
          var $parentData = $dataLvl ? "data" + ($dataLvl - 1 || "") : "parentData", $parentDataProperty = $dataLvl ? it2.dataPathArr[$dataLvl] : "parentDataProperty";
          out += " " + $data + " = " + $coerced + "; ";
          if (!$dataLvl) {
            out += "if (" + $parentData + " !== undefined)";
          }
          out += " " + $parentData + "[" + $parentDataProperty + "] = " + $coerced + "; } ";
        } else {
          var $$outStack = $$outStack || [];
          $$outStack.push(out);
          out = "";
          if (it2.createErrors !== false) {
            out += " { keyword: '" + ($errorKeyword || "type") + "' , dataPath: (dataPath || '') + " + it2.errorPath + " , schemaPath: " + it2.util.toQuotedString($errSchemaPath) + " , params: { type: '";
            if ($typeIsArray) {
              out += "" + $typeSchema.join(",");
            } else {
              out += "" + $typeSchema;
            }
            out += "' } ";
            if (it2.opts.messages !== false) {
              out += " , message: 'should be ";
              if ($typeIsArray) {
                out += "" + $typeSchema.join(",");
              } else {
                out += "" + $typeSchema;
              }
              out += "' ";
            }
            if (it2.opts.verbose) {
              out += " , schema: validate.schema" + $schemaPath + " , parentSchema: validate.schema" + it2.schemaPath + " , data: " + $data + " ";
            }
            out += " } ";
          } else {
            out += " {} ";
          }
          var __err = out;
          out = $$outStack.pop();
          if (!it2.compositeRule && $breakOnError) {
            if (it2.async) {
              out += " throw new ValidationError([" + __err + "]); ";
            } else {
              out += " validate.errors = [" + __err + "]; return false; ";
            }
          } else {
            out += " var err = " + __err + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ";
          }
        }
        out += " } ";
      }
    }
    if (it2.schema.$ref && !$refKeywords) {
      out += " " + it2.RULES.all.$ref.code(it2, "$ref") + " ";
      if ($breakOnError) {
        out += " } if (errors === ";
        if ($top) {
          out += "0";
        } else {
          out += "errs_" + $lvl;
        }
        out += ") { ";
        $closingBraces2 += "}";
      }
    } else {
      var arr2 = it2.RULES;
      if (arr2) {
        var $rulesGroup, i2 = -1, l2 = arr2.length - 1;
        while (i2 < l2) {
          $rulesGroup = arr2[i2 += 1];
          if ($shouldUseGroup($rulesGroup)) {
            if ($rulesGroup.type) {
              out += " if (" + it2.util.checkDataType($rulesGroup.type, $data, it2.opts.strictNumbers) + ") { ";
            }
            if (it2.opts.useDefaults) {
              if ($rulesGroup.type == "object" && it2.schema.properties) {
                var $schema2 = it2.schema.properties, $schemaKeys = Object.keys($schema2);
                var arr3 = $schemaKeys;
                if (arr3) {
                  var $propertyKey, i3 = -1, l3 = arr3.length - 1;
                  while (i3 < l3) {
                    $propertyKey = arr3[i3 += 1];
                    var $sch = $schema2[$propertyKey];
                    if ($sch.default !== void 0) {
                      var $passData = $data + it2.util.getProperty($propertyKey);
                      if (it2.compositeRule) {
                        if (it2.opts.strictDefaults) {
                          var $defaultMsg = "default is ignored for: " + $passData;
                          if (it2.opts.strictDefaults === "log") it2.logger.warn($defaultMsg);
                          else throw new Error($defaultMsg);
                        }
                      } else {
                        out += " if (" + $passData + " === undefined ";
                        if (it2.opts.useDefaults == "empty") {
                          out += " || " + $passData + " === null || " + $passData + " === '' ";
                        }
                        out += " ) " + $passData + " = ";
                        if (it2.opts.useDefaults == "shared") {
                          out += " " + it2.useDefault($sch.default) + " ";
                        } else {
                          out += " " + JSON.stringify($sch.default) + " ";
                        }
                        out += "; ";
                      }
                    }
                  }
                }
              } else if ($rulesGroup.type == "array" && Array.isArray(it2.schema.items)) {
                var arr4 = it2.schema.items;
                if (arr4) {
                  var $sch, $i = -1, l4 = arr4.length - 1;
                  while ($i < l4) {
                    $sch = arr4[$i += 1];
                    if ($sch.default !== void 0) {
                      var $passData = $data + "[" + $i + "]";
                      if (it2.compositeRule) {
                        if (it2.opts.strictDefaults) {
                          var $defaultMsg = "default is ignored for: " + $passData;
                          if (it2.opts.strictDefaults === "log") it2.logger.warn($defaultMsg);
                          else throw new Error($defaultMsg);
                        }
                      } else {
                        out += " if (" + $passData + " === undefined ";
                        if (it2.opts.useDefaults == "empty") {
                          out += " || " + $passData + " === null || " + $passData + " === '' ";
                        }
                        out += " ) " + $passData + " = ";
                        if (it2.opts.useDefaults == "shared") {
                          out += " " + it2.useDefault($sch.default) + " ";
                        } else {
                          out += " " + JSON.stringify($sch.default) + " ";
                        }
                        out += "; ";
                      }
                    }
                  }
                }
              }
            }
            var arr5 = $rulesGroup.rules;
            if (arr5) {
              var $rule, i5 = -1, l5 = arr5.length - 1;
              while (i5 < l5) {
                $rule = arr5[i5 += 1];
                if ($shouldUseRule($rule)) {
                  var $code = $rule.code(it2, $rule.keyword, $rulesGroup.type);
                  if ($code) {
                    out += " " + $code + " ";
                    if ($breakOnError) {
                      $closingBraces1 += "}";
                    }
                  }
                }
              }
            }
            if ($breakOnError) {
              out += " " + $closingBraces1 + " ";
              $closingBraces1 = "";
            }
            if ($rulesGroup.type) {
              out += " } ";
              if ($typeSchema && $typeSchema === $rulesGroup.type && !$coerceToTypes) {
                out += " else { ";
                var $schemaPath = it2.schemaPath + ".type", $errSchemaPath = it2.errSchemaPath + "/type";
                var $$outStack = $$outStack || [];
                $$outStack.push(out);
                out = "";
                if (it2.createErrors !== false) {
                  out += " { keyword: '" + ($errorKeyword || "type") + "' , dataPath: (dataPath || '') + " + it2.errorPath + " , schemaPath: " + it2.util.toQuotedString($errSchemaPath) + " , params: { type: '";
                  if ($typeIsArray) {
                    out += "" + $typeSchema.join(",");
                  } else {
                    out += "" + $typeSchema;
                  }
                  out += "' } ";
                  if (it2.opts.messages !== false) {
                    out += " , message: 'should be ";
                    if ($typeIsArray) {
                      out += "" + $typeSchema.join(",");
                    } else {
                      out += "" + $typeSchema;
                    }
                    out += "' ";
                  }
                  if (it2.opts.verbose) {
                    out += " , schema: validate.schema" + $schemaPath + " , parentSchema: validate.schema" + it2.schemaPath + " , data: " + $data + " ";
                  }
                  out += " } ";
                } else {
                  out += " {} ";
                }
                var __err = out;
                out = $$outStack.pop();
                if (!it2.compositeRule && $breakOnError) {
                  if (it2.async) {
                    out += " throw new ValidationError([" + __err + "]); ";
                  } else {
                    out += " validate.errors = [" + __err + "]; return false; ";
                  }
                } else {
                  out += " var err = " + __err + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ";
                }
                out += " } ";
              }
            }
            if ($breakOnError) {
              out += " if (errors === ";
              if ($top) {
                out += "0";
              } else {
                out += "errs_" + $lvl;
              }
              out += ") { ";
              $closingBraces2 += "}";
            }
          }
        }
      }
    }
    if ($breakOnError) {
      out += " " + $closingBraces2 + " ";
    }
    if ($top) {
      if ($async) {
        out += " if (errors === 0) return data;           ";
        out += " else throw new ValidationError(vErrors); ";
      } else {
        out += " validate.errors = vErrors; ";
        out += " return errors === 0;       ";
      }
      out += " }; return validate;";
    } else {
      out += " var " + $valid + " = errors === errs_" + $lvl + ";";
    }
    function $shouldUseGroup($rulesGroup2) {
      var rules2 = $rulesGroup2.rules;
      for (var i4 = 0; i4 < rules2.length; i4++)
        if ($shouldUseRule(rules2[i4])) return true;
    }
    function $shouldUseRule($rule2) {
      return it2.schema[$rule2.keyword] !== void 0 || $rule2.implements && $ruleImplementsSomeKeyword($rule2);
    }
    function $ruleImplementsSomeKeyword($rule2) {
      var impl = $rule2.implements;
      for (var i4 = 0; i4 < impl.length; i4++)
        if (it2.schema[impl[i4]] !== void 0) return true;
    }
    return out;
  };
  return validate;
}
var compile_1;
var hasRequiredCompile;
function requireCompile() {
  if (hasRequiredCompile) return compile_1;
  hasRequiredCompile = 1;
  var resolve = requireResolve(), util2 = requireUtil(), errorClasses = requireError_classes(), stableStringify = requireFastJsonStableStringify();
  var validateGenerator = requireValidate();
  var ucs2length2 = util2.ucs2length;
  var equal = requireFastDeepEqual();
  var ValidationError = errorClasses.Validation;
  compile_1 = compile;
  function compile(schema, root, localRefs, baseId) {
    var self = this, opts = this._opts, refVal = [void 0], refs = {}, patterns = [], patternsHash = {}, defaults = [], defaultsHash = {}, customRules = [];
    root = root || { schema, refVal, refs };
    var c2 = checkCompiling.call(this, schema, root, baseId);
    var compilation = this._compilations[c2.index];
    if (c2.compiling) return compilation.callValidate = callValidate;
    var formats = this._formats;
    var RULES = this.RULES;
    try {
      var v2 = localCompile(schema, root, localRefs, baseId);
      compilation.validate = v2;
      var cv = compilation.callValidate;
      if (cv) {
        cv.schema = v2.schema;
        cv.errors = null;
        cv.refs = v2.refs;
        cv.refVal = v2.refVal;
        cv.root = v2.root;
        cv.$async = v2.$async;
        if (opts.sourceCode) cv.source = v2.source;
      }
      return v2;
    } finally {
      endCompiling.call(this, schema, root, baseId);
    }
    function callValidate() {
      var validate2 = compilation.validate;
      var result = validate2.apply(this, arguments);
      callValidate.errors = validate2.errors;
      return result;
    }
    function localCompile(_schema, _root, localRefs2, baseId2) {
      var isRoot = !_root || _root && _root.schema == _schema;
      if (_root.schema != root.schema)
        return compile.call(self, _schema, _root, localRefs2, baseId2);
      var $async = _schema.$async === true;
      var sourceCode = validateGenerator({
        isTop: true,
        schema: _schema,
        isRoot,
        baseId: baseId2,
        root: _root,
        schemaPath: "",
        errSchemaPath: "#",
        errorPath: '""',
        MissingRefError: errorClasses.MissingRef,
        RULES,
        validate: validateGenerator,
        util: util2,
        resolve,
        resolveRef,
        usePattern,
        useDefault,
        useCustomRule,
        opts,
        formats,
        logger: self.logger,
        self
      });
      sourceCode = vars(refVal, refValCode) + vars(patterns, patternCode) + vars(defaults, defaultCode) + vars(customRules, customRuleCode) + sourceCode;
      if (opts.processCode) sourceCode = opts.processCode(sourceCode, _schema);
      var validate2;
      try {
        var makeValidate = new Function(
          "self",
          "RULES",
          "formats",
          "root",
          "refVal",
          "defaults",
          "customRules",
          "equal",
          "ucs2length",
          "ValidationError",
          sourceCode
        );
        validate2 = makeValidate(
          self,
          RULES,
          formats,
          root,
          refVal,
          defaults,
          customRules,
          equal,
          ucs2length2,
          ValidationError
        );
        refVal[0] = validate2;
      } catch (e2) {
        self.logger.error("Error compiling schema, function code:", sourceCode);
        throw e2;
      }
      validate2.schema = _schema;
      validate2.errors = null;
      validate2.refs = refs;
      validate2.refVal = refVal;
      validate2.root = isRoot ? validate2 : _root;
      if ($async) validate2.$async = true;
      if (opts.sourceCode === true) {
        validate2.source = {
          code: sourceCode,
          patterns,
          defaults
        };
      }
      return validate2;
    }
    function resolveRef(baseId2, ref2, isRoot) {
      ref2 = resolve.url(baseId2, ref2);
      var refIndex = refs[ref2];
      var _refVal, refCode;
      if (refIndex !== void 0) {
        _refVal = refVal[refIndex];
        refCode = "refVal[" + refIndex + "]";
        return resolvedRef(_refVal, refCode);
      }
      if (!isRoot && root.refs) {
        var rootRefId = root.refs[ref2];
        if (rootRefId !== void 0) {
          _refVal = root.refVal[rootRefId];
          refCode = addLocalRef(ref2, _refVal);
          return resolvedRef(_refVal, refCode);
        }
      }
      refCode = addLocalRef(ref2);
      var v3 = resolve.call(self, localCompile, root, ref2);
      if (v3 === void 0) {
        var localSchema = localRefs && localRefs[ref2];
        if (localSchema) {
          v3 = resolve.inlineRef(localSchema, opts.inlineRefs) ? localSchema : compile.call(self, localSchema, root, localRefs, baseId2);
        }
      }
      if (v3 === void 0) {
        removeLocalRef(ref2);
      } else {
        replaceLocalRef(ref2, v3);
        return resolvedRef(v3, refCode);
      }
    }
    function addLocalRef(ref2, v3) {
      var refId = refVal.length;
      refVal[refId] = v3;
      refs[ref2] = refId;
      return "refVal" + refId;
    }
    function removeLocalRef(ref2) {
      delete refs[ref2];
    }
    function replaceLocalRef(ref2, v3) {
      var refId = refs[ref2];
      refVal[refId] = v3;
    }
    function resolvedRef(refVal2, code) {
      return typeof refVal2 == "object" || typeof refVal2 == "boolean" ? { code, schema: refVal2, inline: true } : { code, $async: refVal2 && !!refVal2.$async };
    }
    function usePattern(regexStr) {
      var index = patternsHash[regexStr];
      if (index === void 0) {
        index = patternsHash[regexStr] = patterns.length;
        patterns[index] = regexStr;
      }
      return "pattern" + index;
    }
    function useDefault(value) {
      switch (typeof value) {
        case "boolean":
        case "number":
          return "" + value;
        case "string":
          return util2.toQuotedString(value);
        case "object":
          if (value === null) return "null";
          var valueStr = stableStringify(value);
          var index = defaultsHash[valueStr];
          if (index === void 0) {
            index = defaultsHash[valueStr] = defaults.length;
            defaults[index] = value;
          }
          return "default" + index;
      }
    }
    function useCustomRule(rule, schema2, parentSchema, it2) {
      if (self._opts.validateSchema !== false) {
        var deps = rule.definition.dependencies;
        if (deps && !deps.every(function(keyword2) {
          return Object.prototype.hasOwnProperty.call(parentSchema, keyword2);
        }))
          throw new Error("parent schema must have all required keywords: " + deps.join(","));
        var validateSchema = rule.definition.validateSchema;
        if (validateSchema) {
          var valid = validateSchema(schema2);
          if (!valid) {
            var message = "keyword schema is invalid: " + self.errorsText(validateSchema.errors);
            if (self._opts.validateSchema == "log") self.logger.error(message);
            else throw new Error(message);
          }
        }
      }
      var compile2 = rule.definition.compile, inline = rule.definition.inline, macro = rule.definition.macro;
      var validate2;
      if (compile2) {
        validate2 = compile2.call(self, schema2, parentSchema, it2);
      } else if (macro) {
        validate2 = macro.call(self, schema2, parentSchema, it2);
        if (opts.validateSchema !== false) self.validateSchema(validate2, true);
      } else if (inline) {
        validate2 = inline.call(self, it2, rule.keyword, schema2, parentSchema);
      } else {
        validate2 = rule.definition.validate;
        if (!validate2) return;
      }
      if (validate2 === void 0)
        throw new Error('custom keyword "' + rule.keyword + '"failed to compile');
      var index = customRules.length;
      customRules[index] = validate2;
      return {
        code: "customRule" + index,
        validate: validate2
      };
    }
  }
  function checkCompiling(schema, root, baseId) {
    var index = compIndex.call(this, schema, root, baseId);
    if (index >= 0) return { index, compiling: true };
    index = this._compilations.length;
    this._compilations[index] = {
      schema,
      root,
      baseId
    };
    return { index, compiling: false };
  }
  function endCompiling(schema, root, baseId) {
    var i2 = compIndex.call(this, schema, root, baseId);
    if (i2 >= 0) this._compilations.splice(i2, 1);
  }
  function compIndex(schema, root, baseId) {
    for (var i2 = 0; i2 < this._compilations.length; i2++) {
      var c2 = this._compilations[i2];
      if (c2.schema == schema && c2.root == root && c2.baseId == baseId) return i2;
    }
    return -1;
  }
  function patternCode(i2, patterns) {
    return "var pattern" + i2 + " = new RegExp(" + util2.toQuotedString(patterns[i2]) + ");";
  }
  function defaultCode(i2) {
    return "var default" + i2 + " = defaults[" + i2 + "];";
  }
  function refValCode(i2, refVal) {
    return refVal[i2] === void 0 ? "" : "var refVal" + i2 + " = refVal[" + i2 + "];";
  }
  function customRuleCode(i2) {
    return "var customRule" + i2 + " = customRules[" + i2 + "];";
  }
  function vars(arr, statement) {
    if (!arr.length) return "";
    var code = "";
    for (var i2 = 0; i2 < arr.length; i2++)
      code += statement(i2, arr);
    return code;
  }
  return compile_1;
}
var cache = { exports: {} };
var hasRequiredCache;
function requireCache() {
  if (hasRequiredCache) return cache.exports;
  hasRequiredCache = 1;
  var Cache = cache.exports = function Cache2() {
    this._cache = {};
  };
  Cache.prototype.put = function Cache_put(key, value) {
    this._cache[key] = value;
  };
  Cache.prototype.get = function Cache_get(key) {
    return this._cache[key];
  };
  Cache.prototype.del = function Cache_del(key) {
    delete this._cache[key];
  };
  Cache.prototype.clear = function Cache_clear() {
    this._cache = {};
  };
  return cache.exports;
}
var formats_1;
var hasRequiredFormats;
function requireFormats() {
  if (hasRequiredFormats) return formats_1;
  hasRequiredFormats = 1;
  var util2 = requireUtil();
  var DATE = /^(\d\d\d\d)-(\d\d)-(\d\d)$/;
  var DAYS = [0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
  var TIME = /^(\d\d):(\d\d):(\d\d)(\.\d+)?(z|[+-]\d\d(?::?\d\d)?)?$/i;
  var HOSTNAME = /^(?=.{1,253}\.?$)[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?(?:\.[a-z0-9](?:[-0-9a-z]{0,61}[0-9a-z])?)*\.?$/i;
  var URI = /^(?:[a-z][a-z0-9+\-.]*:)(?:\/?\/(?:(?:[a-z0-9\-._~!$&'()*+,;=:]|%[0-9a-f]{2})*@)?(?:\[(?:(?:(?:(?:[0-9a-f]{1,4}:){6}|::(?:[0-9a-f]{1,4}:){5}|(?:[0-9a-f]{1,4})?::(?:[0-9a-f]{1,4}:){4}|(?:(?:[0-9a-f]{1,4}:){0,1}[0-9a-f]{1,4})?::(?:[0-9a-f]{1,4}:){3}|(?:(?:[0-9a-f]{1,4}:){0,2}[0-9a-f]{1,4})?::(?:[0-9a-f]{1,4}:){2}|(?:(?:[0-9a-f]{1,4}:){0,3}[0-9a-f]{1,4})?::[0-9a-f]{1,4}:|(?:(?:[0-9a-f]{1,4}:){0,4}[0-9a-f]{1,4})?::)(?:[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:(?:25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(?:25[0-5]|2[0-4]\d|[01]?\d\d?))|(?:(?:[0-9a-f]{1,4}:){0,5}[0-9a-f]{1,4})?::[0-9a-f]{1,4}|(?:(?:[0-9a-f]{1,4}:){0,6}[0-9a-f]{1,4})?::)|[Vv][0-9a-f]+\.[a-z0-9\-._~!$&'()*+,;=:]+)\]|(?:(?:25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(?:25[0-5]|2[0-4]\d|[01]?\d\d?)|(?:[a-z0-9\-._~!$&'()*+,;=]|%[0-9a-f]{2})*)(?::\d*)?(?:\/(?:[a-z0-9\-._~!$&'()*+,;=:@]|%[0-9a-f]{2})*)*|\/(?:(?:[a-z0-9\-._~!$&'()*+,;=:@]|%[0-9a-f]{2})+(?:\/(?:[a-z0-9\-._~!$&'()*+,;=:@]|%[0-9a-f]{2})*)*)?|(?:[a-z0-9\-._~!$&'()*+,;=:@]|%[0-9a-f]{2})+(?:\/(?:[a-z0-9\-._~!$&'()*+,;=:@]|%[0-9a-f]{2})*)*)(?:\?(?:[a-z0-9\-._~!$&'()*+,;=:@/?]|%[0-9a-f]{2})*)?(?:#(?:[a-z0-9\-._~!$&'()*+,;=:@/?]|%[0-9a-f]{2})*)?$/i;
  var URIREF = /^(?:[a-z][a-z0-9+\-.]*:)?(?:\/?\/(?:(?:[a-z0-9\-._~!$&'()*+,;=:]|%[0-9a-f]{2})*@)?(?:\[(?:(?:(?:(?:[0-9a-f]{1,4}:){6}|::(?:[0-9a-f]{1,4}:){5}|(?:[0-9a-f]{1,4})?::(?:[0-9a-f]{1,4}:){4}|(?:(?:[0-9a-f]{1,4}:){0,1}[0-9a-f]{1,4})?::(?:[0-9a-f]{1,4}:){3}|(?:(?:[0-9a-f]{1,4}:){0,2}[0-9a-f]{1,4})?::(?:[0-9a-f]{1,4}:){2}|(?:(?:[0-9a-f]{1,4}:){0,3}[0-9a-f]{1,4})?::[0-9a-f]{1,4}:|(?:(?:[0-9a-f]{1,4}:){0,4}[0-9a-f]{1,4})?::)(?:[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:(?:25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(?:25[0-5]|2[0-4]\d|[01]?\d\d?))|(?:(?:[0-9a-f]{1,4}:){0,5}[0-9a-f]{1,4})?::[0-9a-f]{1,4}|(?:(?:[0-9a-f]{1,4}:){0,6}[0-9a-f]{1,4})?::)|[Vv][0-9a-f]+\.[a-z0-9\-._~!$&'()*+,;=:]+)\]|(?:(?:25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(?:25[0-5]|2[0-4]\d|[01]?\d\d?)|(?:[a-z0-9\-._~!$&'"()*+,;=]|%[0-9a-f]{2})*)(?::\d*)?(?:\/(?:[a-z0-9\-._~!$&'"()*+,;=:@]|%[0-9a-f]{2})*)*|\/(?:(?:[a-z0-9\-._~!$&'"()*+,;=:@]|%[0-9a-f]{2})+(?:\/(?:[a-z0-9\-._~!$&'"()*+,;=:@]|%[0-9a-f]{2})*)*)?|(?:[a-z0-9\-._~!$&'"()*+,;=:@]|%[0-9a-f]{2})+(?:\/(?:[a-z0-9\-._~!$&'"()*+,;=:@]|%[0-9a-f]{2})*)*)?(?:\?(?:[a-z0-9\-._~!$&'"()*+,;=:@/?]|%[0-9a-f]{2})*)?(?:#(?:[a-z0-9\-._~!$&'"()*+,;=:@/?]|%[0-9a-f]{2})*)?$/i;
  var URITEMPLATE = /^(?:(?:[^\x00-\x20"'<>%\\^`{|}]|%[0-9a-f]{2})|\{[+#./;?&=,!@|]?(?:[a-z0-9_]|%[0-9a-f]{2})+(?::[1-9][0-9]{0,3}|\*)?(?:,(?:[a-z0-9_]|%[0-9a-f]{2})+(?::[1-9][0-9]{0,3}|\*)?)*\})*$/i;
  var URL2 = /^(?:(?:http[s\u017F]?|ftp):\/\/)(?:(?:[\0-\x08\x0E-\x1F!-\x9F\xA1-\u167F\u1681-\u1FFF\u200B-\u2027\u202A-\u202E\u2030-\u205E\u2060-\u2FFF\u3001-\uD7FF\uE000-\uFEFE\uFF00-\uFFFF]|[\uD800-\uDBFF][\uDC00-\uDFFF]|[\uD800-\uDBFF](?![\uDC00-\uDFFF])|(?:[^\uD800-\uDBFF]|^)[\uDC00-\uDFFF])+(?::(?:[\0-\x08\x0E-\x1F!-\x9F\xA1-\u167F\u1681-\u1FFF\u200B-\u2027\u202A-\u202E\u2030-\u205E\u2060-\u2FFF\u3001-\uD7FF\uE000-\uFEFE\uFF00-\uFFFF]|[\uD800-\uDBFF][\uDC00-\uDFFF]|[\uD800-\uDBFF](?![\uDC00-\uDFFF])|(?:[^\uD800-\uDBFF]|^)[\uDC00-\uDFFF])*)?@)?(?:(?!10(?:\.[0-9]{1,3}){3})(?!127(?:\.[0-9]{1,3}){3})(?!169\.254(?:\.[0-9]{1,3}){2})(?!192\.168(?:\.[0-9]{1,3}){2})(?!172\.(?:1[6-9]|2[0-9]|3[01])(?:\.[0-9]{1,3}){2})(?:[1-9][0-9]?|1[0-9][0-9]|2[01][0-9]|22[0-3])(?:\.(?:1?[0-9]{1,2}|2[0-4][0-9]|25[0-5])){2}(?:\.(?:[1-9][0-9]?|1[0-9][0-9]|2[0-4][0-9]|25[0-4]))|(?:(?:(?:[0-9a-z\xA1-\uD7FF\uE000-\uFFFF]|[\uD800-\uDBFF](?![\uDC00-\uDFFF])|(?:[^\uD800-\uDBFF]|^)[\uDC00-\uDFFF])+-)*(?:[0-9a-z\xA1-\uD7FF\uE000-\uFFFF]|[\uD800-\uDBFF](?![\uDC00-\uDFFF])|(?:[^\uD800-\uDBFF]|^)[\uDC00-\uDFFF])+)(?:\.(?:(?:[0-9a-z\xA1-\uD7FF\uE000-\uFFFF]|[\uD800-\uDBFF](?![\uDC00-\uDFFF])|(?:[^\uD800-\uDBFF]|^)[\uDC00-\uDFFF])+-)*(?:[0-9a-z\xA1-\uD7FF\uE000-\uFFFF]|[\uD800-\uDBFF](?![\uDC00-\uDFFF])|(?:[^\uD800-\uDBFF]|^)[\uDC00-\uDFFF])+)*(?:\.(?:(?:[a-z\xA1-\uD7FF\uE000-\uFFFF]|[\uD800-\uDBFF](?![\uDC00-\uDFFF])|(?:[^\uD800-\uDBFF]|^)[\uDC00-\uDFFF]){2,})))(?::[0-9]{2,5})?(?:\/(?:[\0-\x08\x0E-\x1F!-\x9F\xA1-\u167F\u1681-\u1FFF\u200B-\u2027\u202A-\u202E\u2030-\u205E\u2060-\u2FFF\u3001-\uD7FF\uE000-\uFEFE\uFF00-\uFFFF]|[\uD800-\uDBFF][\uDC00-\uDFFF]|[\uD800-\uDBFF](?![\uDC00-\uDFFF])|(?:[^\uD800-\uDBFF]|^)[\uDC00-\uDFFF])*)?$/i;
  var UUID = /^(?:urn:uuid:)?[0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12}$/i;
  var JSON_POINTER = /^(?:\/(?:[^~/]|~0|~1)*)*$/;
  var JSON_POINTER_URI_FRAGMENT = /^#(?:\/(?:[a-z0-9_\-.!$&'()*+,;:=@]|%[0-9a-f]{2}|~0|~1)*)*$/i;
  var RELATIVE_JSON_POINTER = /^(?:0|[1-9][0-9]*)(?:#|(?:\/(?:[^~/]|~0|~1)*)*)$/;
  formats_1 = formats;
  function formats(mode) {
    mode = mode == "full" ? "full" : "fast";
    return util2.copy(formats[mode]);
  }
  formats.fast = {
    // date: http://tools.ietf.org/html/rfc3339#section-5.6
    date: /^\d\d\d\d-[0-1]\d-[0-3]\d$/,
    // date-time: http://tools.ietf.org/html/rfc3339#section-5.6
    time: /^(?:[0-2]\d:[0-5]\d:[0-5]\d|23:59:60)(?:\.\d+)?(?:z|[+-]\d\d(?::?\d\d)?)?$/i,
    "date-time": /^\d\d\d\d-[0-1]\d-[0-3]\d[t\s](?:[0-2]\d:[0-5]\d:[0-5]\d|23:59:60)(?:\.\d+)?(?:z|[+-]\d\d(?::?\d\d)?)$/i,
    // uri: https://github.com/mafintosh/is-my-json-valid/blob/master/formats.js
    uri: /^(?:[a-z][a-z0-9+\-.]*:)(?:\/?\/)?[^\s]*$/i,
    "uri-reference": /^(?:(?:[a-z][a-z0-9+\-.]*:)?\/?\/)?(?:[^\\\s#][^\s#]*)?(?:#[^\\\s]*)?$/i,
    "uri-template": URITEMPLATE,
    url: URL2,
    // email (sources from jsen validator):
    // http://stackoverflow.com/questions/201323/using-a-regular-expression-to-validate-an-email-address#answer-8829363
    // http://www.w3.org/TR/html5/forms.html#valid-e-mail-address (search for 'willful violation')
    email: /^[a-z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?(?:\.[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)*$/i,
    hostname: HOSTNAME,
    // optimized https://www.safaribooksonline.com/library/view/regular-expressions-cookbook/9780596802837/ch07s16.html
    ipv4: /^(?:(?:25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(?:25[0-5]|2[0-4]\d|[01]?\d\d?)$/,
    // optimized http://stackoverflow.com/questions/53497/regular-expression-that-matches-valid-ipv6-addresses
    ipv6: /^\s*(?:(?:(?:[0-9a-f]{1,4}:){7}(?:[0-9a-f]{1,4}|:))|(?:(?:[0-9a-f]{1,4}:){6}(?::[0-9a-f]{1,4}|(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(?:(?:[0-9a-f]{1,4}:){5}(?:(?:(?::[0-9a-f]{1,4}){1,2})|:(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(?:(?:[0-9a-f]{1,4}:){4}(?:(?:(?::[0-9a-f]{1,4}){1,3})|(?:(?::[0-9a-f]{1,4})?:(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(?:(?:[0-9a-f]{1,4}:){3}(?:(?:(?::[0-9a-f]{1,4}){1,4})|(?:(?::[0-9a-f]{1,4}){0,2}:(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(?:(?:[0-9a-f]{1,4}:){2}(?:(?:(?::[0-9a-f]{1,4}){1,5})|(?:(?::[0-9a-f]{1,4}){0,3}:(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(?:(?:[0-9a-f]{1,4}:){1}(?:(?:(?::[0-9a-f]{1,4}){1,6})|(?:(?::[0-9a-f]{1,4}){0,4}:(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(?::(?:(?:(?::[0-9a-f]{1,4}){1,7})|(?:(?::[0-9a-f]{1,4}){0,5}:(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(?:%.+)?\s*$/i,
    regex,
    // uuid: http://tools.ietf.org/html/rfc4122
    uuid: UUID,
    // JSON-pointer: https://tools.ietf.org/html/rfc6901
    // uri fragment: https://tools.ietf.org/html/rfc3986#appendix-A
    "json-pointer": JSON_POINTER,
    "json-pointer-uri-fragment": JSON_POINTER_URI_FRAGMENT,
    // relative JSON-pointer: http://tools.ietf.org/html/draft-luff-relative-json-pointer-00
    "relative-json-pointer": RELATIVE_JSON_POINTER
  };
  formats.full = {
    date,
    time,
    "date-time": date_time,
    uri,
    "uri-reference": URIREF,
    "uri-template": URITEMPLATE,
    url: URL2,
    email: /^[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/i,
    hostname: HOSTNAME,
    ipv4: /^(?:(?:25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(?:25[0-5]|2[0-4]\d|[01]?\d\d?)$/,
    ipv6: /^\s*(?:(?:(?:[0-9a-f]{1,4}:){7}(?:[0-9a-f]{1,4}|:))|(?:(?:[0-9a-f]{1,4}:){6}(?::[0-9a-f]{1,4}|(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(?:(?:[0-9a-f]{1,4}:){5}(?:(?:(?::[0-9a-f]{1,4}){1,2})|:(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(?:(?:[0-9a-f]{1,4}:){4}(?:(?:(?::[0-9a-f]{1,4}){1,3})|(?:(?::[0-9a-f]{1,4})?:(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(?:(?:[0-9a-f]{1,4}:){3}(?:(?:(?::[0-9a-f]{1,4}){1,4})|(?:(?::[0-9a-f]{1,4}){0,2}:(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(?:(?:[0-9a-f]{1,4}:){2}(?:(?:(?::[0-9a-f]{1,4}){1,5})|(?:(?::[0-9a-f]{1,4}){0,3}:(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(?:(?:[0-9a-f]{1,4}:){1}(?:(?:(?::[0-9a-f]{1,4}){1,6})|(?:(?::[0-9a-f]{1,4}){0,4}:(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(?::(?:(?:(?::[0-9a-f]{1,4}){1,7})|(?:(?::[0-9a-f]{1,4}){0,5}:(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(?:%.+)?\s*$/i,
    regex,
    uuid: UUID,
    "json-pointer": JSON_POINTER,
    "json-pointer-uri-fragment": JSON_POINTER_URI_FRAGMENT,
    "relative-json-pointer": RELATIVE_JSON_POINTER
  };
  function isLeapYear(year) {
    return year % 4 === 0 && (year % 100 !== 0 || year % 400 === 0);
  }
  function date(str) {
    var matches = str.match(DATE);
    if (!matches) return false;
    var year = +matches[1];
    var month = +matches[2];
    var day = +matches[3];
    return month >= 1 && month <= 12 && day >= 1 && day <= (month == 2 && isLeapYear(year) ? 29 : DAYS[month]);
  }
  function time(str, full) {
    var matches = str.match(TIME);
    if (!matches) return false;
    var hour = matches[1];
    var minute = matches[2];
    var second = matches[3];
    var timeZone = matches[5];
    return (hour <= 23 && minute <= 59 && second <= 59 || hour == 23 && minute == 59 && second == 60) && (!full || timeZone);
  }
  var DATE_TIME_SEPARATOR = /t|\s/i;
  function date_time(str) {
    var dateTime = str.split(DATE_TIME_SEPARATOR);
    return dateTime.length == 2 && date(dateTime[0]) && time(dateTime[1], true);
  }
  var NOT_URI_FRAGMENT = /\/|:/;
  function uri(str) {
    return NOT_URI_FRAGMENT.test(str) && URI.test(str);
  }
  var Z_ANCHOR = /[^\\]\\Z/;
  function regex(str) {
    if (Z_ANCHOR.test(str)) return false;
    try {
      new RegExp(str);
      return true;
    } catch (e2) {
      return false;
    }
  }
  return formats_1;
}
var ref;
var hasRequiredRef;
function requireRef() {
  if (hasRequiredRef) return ref;
  hasRequiredRef = 1;
  ref = function generate_ref(it2, $keyword, $ruleType) {
    var out = " ";
    var $lvl = it2.level;
    var $dataLvl = it2.dataLevel;
    var $schema2 = it2.schema[$keyword];
    var $errSchemaPath = it2.errSchemaPath + "/" + $keyword;
    var $breakOnError = !it2.opts.allErrors;
    var $data = "data" + ($dataLvl || "");
    var $valid = "valid" + $lvl;
    var $async, $refCode;
    if ($schema2 == "#" || $schema2 == "#/") {
      if (it2.isRoot) {
        $async = it2.async;
        $refCode = "validate";
      } else {
        $async = it2.root.schema.$async === true;
        $refCode = "root.refVal[0]";
      }
    } else {
      var $refVal = it2.resolveRef(it2.baseId, $schema2, it2.isRoot);
      if ($refVal === void 0) {
        var $message = it2.MissingRefError.message(it2.baseId, $schema2);
        if (it2.opts.missingRefs == "fail") {
          it2.logger.error($message);
          var $$outStack = $$outStack || [];
          $$outStack.push(out);
          out = "";
          if (it2.createErrors !== false) {
            out += " { keyword: '$ref' , dataPath: (dataPath || '') + " + it2.errorPath + " , schemaPath: " + it2.util.toQuotedString($errSchemaPath) + " , params: { ref: '" + it2.util.escapeQuotes($schema2) + "' } ";
            if (it2.opts.messages !== false) {
              out += " , message: 'can\\'t resolve reference " + it2.util.escapeQuotes($schema2) + "' ";
            }
            if (it2.opts.verbose) {
              out += " , schema: " + it2.util.toQuotedString($schema2) + " , parentSchema: validate.schema" + it2.schemaPath + " , data: " + $data + " ";
            }
            out += " } ";
          } else {
            out += " {} ";
          }
          var __err = out;
          out = $$outStack.pop();
          if (!it2.compositeRule && $breakOnError) {
            if (it2.async) {
              out += " throw new ValidationError([" + __err + "]); ";
            } else {
              out += " validate.errors = [" + __err + "]; return false; ";
            }
          } else {
            out += " var err = " + __err + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ";
          }
          if ($breakOnError) {
            out += " if (false) { ";
          }
        } else if (it2.opts.missingRefs == "ignore") {
          it2.logger.warn($message);
          if ($breakOnError) {
            out += " if (true) { ";
          }
        } else {
          throw new it2.MissingRefError(it2.baseId, $schema2, $message);
        }
      } else if ($refVal.inline) {
        var $it = it2.util.copy(it2);
        $it.level++;
        var $nextValid = "valid" + $it.level;
        $it.schema = $refVal.schema;
        $it.schemaPath = "";
        $it.errSchemaPath = $schema2;
        var $code = it2.validate($it).replace(/validate\.schema/g, $refVal.code);
        out += " " + $code + " ";
        if ($breakOnError) {
          out += " if (" + $nextValid + ") { ";
        }
      } else {
        $async = $refVal.$async === true || it2.async && $refVal.$async !== false;
        $refCode = $refVal.code;
      }
    }
    if ($refCode) {
      var $$outStack = $$outStack || [];
      $$outStack.push(out);
      out = "";
      if (it2.opts.passContext) {
        out += " " + $refCode + ".call(this, ";
      } else {
        out += " " + $refCode + "( ";
      }
      out += " " + $data + ", (dataPath || '')";
      if (it2.errorPath != '""') {
        out += " + " + it2.errorPath;
      }
      var $parentData = $dataLvl ? "data" + ($dataLvl - 1 || "") : "parentData", $parentDataProperty = $dataLvl ? it2.dataPathArr[$dataLvl] : "parentDataProperty";
      out += " , " + $parentData + " , " + $parentDataProperty + ", rootData)  ";
      var __callValidate = out;
      out = $$outStack.pop();
      if ($async) {
        if (!it2.async) throw new Error("async schema referenced by sync schema");
        if ($breakOnError) {
          out += " var " + $valid + "; ";
        }
        out += " try { await " + __callValidate + "; ";
        if ($breakOnError) {
          out += " " + $valid + " = true; ";
        }
        out += " } catch (e) { if (!(e instanceof ValidationError)) throw e; if (vErrors === null) vErrors = e.errors; else vErrors = vErrors.concat(e.errors); errors = vErrors.length; ";
        if ($breakOnError) {
          out += " " + $valid + " = false; ";
        }
        out += " } ";
        if ($breakOnError) {
          out += " if (" + $valid + ") { ";
        }
      } else {
        out += " if (!" + __callValidate + ") { if (vErrors === null) vErrors = " + $refCode + ".errors; else vErrors = vErrors.concat(" + $refCode + ".errors); errors = vErrors.length; } ";
        if ($breakOnError) {
          out += " else { ";
        }
      }
    }
    return out;
  };
  return ref;
}
var allOf;
var hasRequiredAllOf;
function requireAllOf() {
  if (hasRequiredAllOf) return allOf;
  hasRequiredAllOf = 1;
  allOf = function generate_allOf(it2, $keyword, $ruleType) {
    var out = " ";
    var $schema2 = it2.schema[$keyword];
    var $schemaPath = it2.schemaPath + it2.util.getProperty($keyword);
    var $errSchemaPath = it2.errSchemaPath + "/" + $keyword;
    var $breakOnError = !it2.opts.allErrors;
    var $it = it2.util.copy(it2);
    var $closingBraces = "";
    $it.level++;
    var $nextValid = "valid" + $it.level;
    var $currentBaseId = $it.baseId, $allSchemasEmpty = true;
    var arr1 = $schema2;
    if (arr1) {
      var $sch, $i = -1, l1 = arr1.length - 1;
      while ($i < l1) {
        $sch = arr1[$i += 1];
        if (it2.opts.strictKeywords ? typeof $sch == "object" && Object.keys($sch).length > 0 || $sch === false : it2.util.schemaHasRules($sch, it2.RULES.all)) {
          $allSchemasEmpty = false;
          $it.schema = $sch;
          $it.schemaPath = $schemaPath + "[" + $i + "]";
          $it.errSchemaPath = $errSchemaPath + "/" + $i;
          out += "  " + it2.validate($it) + " ";
          $it.baseId = $currentBaseId;
          if ($breakOnError) {
            out += " if (" + $nextValid + ") { ";
            $closingBraces += "}";
          }
        }
      }
    }
    if ($breakOnError) {
      if ($allSchemasEmpty) {
        out += " if (true) { ";
      } else {
        out += " " + $closingBraces.slice(0, -1) + " ";
      }
    }
    return out;
  };
  return allOf;
}
var anyOf;
var hasRequiredAnyOf;
function requireAnyOf() {
  if (hasRequiredAnyOf) return anyOf;
  hasRequiredAnyOf = 1;
  anyOf = function generate_anyOf(it2, $keyword, $ruleType) {
    var out = " ";
    var $lvl = it2.level;
    var $dataLvl = it2.dataLevel;
    var $schema2 = it2.schema[$keyword];
    var $schemaPath = it2.schemaPath + it2.util.getProperty($keyword);
    var $errSchemaPath = it2.errSchemaPath + "/" + $keyword;
    var $breakOnError = !it2.opts.allErrors;
    var $data = "data" + ($dataLvl || "");
    var $valid = "valid" + $lvl;
    var $errs = "errs__" + $lvl;
    var $it = it2.util.copy(it2);
    var $closingBraces = "";
    $it.level++;
    var $nextValid = "valid" + $it.level;
    var $noEmptySchema = $schema2.every(function($sch2) {
      return it2.opts.strictKeywords ? typeof $sch2 == "object" && Object.keys($sch2).length > 0 || $sch2 === false : it2.util.schemaHasRules($sch2, it2.RULES.all);
    });
    if ($noEmptySchema) {
      var $currentBaseId = $it.baseId;
      out += " var " + $errs + " = errors; var " + $valid + " = false;  ";
      var $wasComposite = it2.compositeRule;
      it2.compositeRule = $it.compositeRule = true;
      var arr1 = $schema2;
      if (arr1) {
        var $sch, $i = -1, l1 = arr1.length - 1;
        while ($i < l1) {
          $sch = arr1[$i += 1];
          $it.schema = $sch;
          $it.schemaPath = $schemaPath + "[" + $i + "]";
          $it.errSchemaPath = $errSchemaPath + "/" + $i;
          out += "  " + it2.validate($it) + " ";
          $it.baseId = $currentBaseId;
          out += " " + $valid + " = " + $valid + " || " + $nextValid + "; if (!" + $valid + ") { ";
          $closingBraces += "}";
        }
      }
      it2.compositeRule = $it.compositeRule = $wasComposite;
      out += " " + $closingBraces + " if (!" + $valid + ") {   var err =   ";
      if (it2.createErrors !== false) {
        out += " { keyword: 'anyOf' , dataPath: (dataPath || '') + " + it2.errorPath + " , schemaPath: " + it2.util.toQuotedString($errSchemaPath) + " , params: {} ";
        if (it2.opts.messages !== false) {
          out += " , message: 'should match some schema in anyOf' ";
        }
        if (it2.opts.verbose) {
          out += " , schema: validate.schema" + $schemaPath + " , parentSchema: validate.schema" + it2.schemaPath + " , data: " + $data + " ";
        }
        out += " } ";
      } else {
        out += " {} ";
      }
      out += ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ";
      if (!it2.compositeRule && $breakOnError) {
        if (it2.async) {
          out += " throw new ValidationError(vErrors); ";
        } else {
          out += " validate.errors = vErrors; return false; ";
        }
      }
      out += " } else {  errors = " + $errs + "; if (vErrors !== null) { if (" + $errs + ") vErrors.length = " + $errs + "; else vErrors = null; } ";
      if (it2.opts.allErrors) {
        out += " } ";
      }
    } else {
      if ($breakOnError) {
        out += " if (true) { ";
      }
    }
    return out;
  };
  return anyOf;
}
var comment;
var hasRequiredComment;
function requireComment() {
  if (hasRequiredComment) return comment;
  hasRequiredComment = 1;
  comment = function generate_comment(it2, $keyword, $ruleType) {
    var out = " ";
    var $schema2 = it2.schema[$keyword];
    var $errSchemaPath = it2.errSchemaPath + "/" + $keyword;
    !it2.opts.allErrors;
    var $comment = it2.util.toQuotedString($schema2);
    if (it2.opts.$comment === true) {
      out += " console.log(" + $comment + ");";
    } else if (typeof it2.opts.$comment == "function") {
      out += " self._opts.$comment(" + $comment + ", " + it2.util.toQuotedString($errSchemaPath) + ", validate.root.schema);";
    }
    return out;
  };
  return comment;
}
var _const;
var hasRequired_const;
function require_const() {
  if (hasRequired_const) return _const;
  hasRequired_const = 1;
  _const = function generate_const(it2, $keyword, $ruleType) {
    var out = " ";
    var $lvl = it2.level;
    var $dataLvl = it2.dataLevel;
    var $schema2 = it2.schema[$keyword];
    var $schemaPath = it2.schemaPath + it2.util.getProperty($keyword);
    var $errSchemaPath = it2.errSchemaPath + "/" + $keyword;
    var $breakOnError = !it2.opts.allErrors;
    var $data = "data" + ($dataLvl || "");
    var $valid = "valid" + $lvl;
    var $isData = it2.opts.$data && $schema2 && $schema2.$data;
    if ($isData) {
      out += " var schema" + $lvl + " = " + it2.util.getData($schema2.$data, $dataLvl, it2.dataPathArr) + "; ";
    }
    if (!$isData) {
      out += " var schema" + $lvl + " = validate.schema" + $schemaPath + ";";
    }
    out += "var " + $valid + " = equal(" + $data + ", schema" + $lvl + "); if (!" + $valid + ") {   ";
    var $$outStack = $$outStack || [];
    $$outStack.push(out);
    out = "";
    if (it2.createErrors !== false) {
      out += " { keyword: 'const' , dataPath: (dataPath || '') + " + it2.errorPath + " , schemaPath: " + it2.util.toQuotedString($errSchemaPath) + " , params: { allowedValue: schema" + $lvl + " } ";
      if (it2.opts.messages !== false) {
        out += " , message: 'should be equal to constant' ";
      }
      if (it2.opts.verbose) {
        out += " , schema: validate.schema" + $schemaPath + " , parentSchema: validate.schema" + it2.schemaPath + " , data: " + $data + " ";
      }
      out += " } ";
    } else {
      out += " {} ";
    }
    var __err = out;
    out = $$outStack.pop();
    if (!it2.compositeRule && $breakOnError) {
      if (it2.async) {
        out += " throw new ValidationError([" + __err + "]); ";
      } else {
        out += " validate.errors = [" + __err + "]; return false; ";
      }
    } else {
      out += " var err = " + __err + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ";
    }
    out += " }";
    if ($breakOnError) {
      out += " else { ";
    }
    return out;
  };
  return _const;
}
var contains;
var hasRequiredContains;
function requireContains() {
  if (hasRequiredContains) return contains;
  hasRequiredContains = 1;
  contains = function generate_contains(it2, $keyword, $ruleType) {
    var out = " ";
    var $lvl = it2.level;
    var $dataLvl = it2.dataLevel;
    var $schema2 = it2.schema[$keyword];
    var $schemaPath = it2.schemaPath + it2.util.getProperty($keyword);
    var $errSchemaPath = it2.errSchemaPath + "/" + $keyword;
    var $breakOnError = !it2.opts.allErrors;
    var $data = "data" + ($dataLvl || "");
    var $valid = "valid" + $lvl;
    var $errs = "errs__" + $lvl;
    var $it = it2.util.copy(it2);
    var $closingBraces = "";
    $it.level++;
    var $nextValid = "valid" + $it.level;
    var $idx = "i" + $lvl, $dataNxt = $it.dataLevel = it2.dataLevel + 1, $nextData = "data" + $dataNxt, $currentBaseId = it2.baseId, $nonEmptySchema = it2.opts.strictKeywords ? typeof $schema2 == "object" && Object.keys($schema2).length > 0 || $schema2 === false : it2.util.schemaHasRules($schema2, it2.RULES.all);
    out += "var " + $errs + " = errors;var " + $valid + ";";
    if ($nonEmptySchema) {
      var $wasComposite = it2.compositeRule;
      it2.compositeRule = $it.compositeRule = true;
      $it.schema = $schema2;
      $it.schemaPath = $schemaPath;
      $it.errSchemaPath = $errSchemaPath;
      out += " var " + $nextValid + " = false; for (var " + $idx + " = 0; " + $idx + " < " + $data + ".length; " + $idx + "++) { ";
      $it.errorPath = it2.util.getPathExpr(it2.errorPath, $idx, it2.opts.jsonPointers, true);
      var $passData = $data + "[" + $idx + "]";
      $it.dataPathArr[$dataNxt] = $idx;
      var $code = it2.validate($it);
      $it.baseId = $currentBaseId;
      if (it2.util.varOccurences($code, $nextData) < 2) {
        out += " " + it2.util.varReplace($code, $nextData, $passData) + " ";
      } else {
        out += " var " + $nextData + " = " + $passData + "; " + $code + " ";
      }
      out += " if (" + $nextValid + ") break; }  ";
      it2.compositeRule = $it.compositeRule = $wasComposite;
      out += " " + $closingBraces + " if (!" + $nextValid + ") {";
    } else {
      out += " if (" + $data + ".length == 0) {";
    }
    var $$outStack = $$outStack || [];
    $$outStack.push(out);
    out = "";
    if (it2.createErrors !== false) {
      out += " { keyword: 'contains' , dataPath: (dataPath || '') + " + it2.errorPath + " , schemaPath: " + it2.util.toQuotedString($errSchemaPath) + " , params: {} ";
      if (it2.opts.messages !== false) {
        out += " , message: 'should contain a valid item' ";
      }
      if (it2.opts.verbose) {
        out += " , schema: validate.schema" + $schemaPath + " , parentSchema: validate.schema" + it2.schemaPath + " , data: " + $data + " ";
      }
      out += " } ";
    } else {
      out += " {} ";
    }
    var __err = out;
    out = $$outStack.pop();
    if (!it2.compositeRule && $breakOnError) {
      if (it2.async) {
        out += " throw new ValidationError([" + __err + "]); ";
      } else {
        out += " validate.errors = [" + __err + "]; return false; ";
      }
    } else {
      out += " var err = " + __err + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ";
    }
    out += " } else { ";
    if ($nonEmptySchema) {
      out += "  errors = " + $errs + "; if (vErrors !== null) { if (" + $errs + ") vErrors.length = " + $errs + "; else vErrors = null; } ";
    }
    if (it2.opts.allErrors) {
      out += " } ";
    }
    return out;
  };
  return contains;
}
var dependencies;
var hasRequiredDependencies;
function requireDependencies() {
  if (hasRequiredDependencies) return dependencies;
  hasRequiredDependencies = 1;
  dependencies = function generate_dependencies(it2, $keyword, $ruleType) {
    var out = " ";
    var $lvl = it2.level;
    var $dataLvl = it2.dataLevel;
    var $schema2 = it2.schema[$keyword];
    var $schemaPath = it2.schemaPath + it2.util.getProperty($keyword);
    var $errSchemaPath = it2.errSchemaPath + "/" + $keyword;
    var $breakOnError = !it2.opts.allErrors;
    var $data = "data" + ($dataLvl || "");
    var $errs = "errs__" + $lvl;
    var $it = it2.util.copy(it2);
    var $closingBraces = "";
    $it.level++;
    var $nextValid = "valid" + $it.level;
    var $schemaDeps = {}, $propertyDeps = {}, $ownProperties = it2.opts.ownProperties;
    for ($property in $schema2) {
      if ($property == "__proto__") continue;
      var $sch = $schema2[$property];
      var $deps = Array.isArray($sch) ? $propertyDeps : $schemaDeps;
      $deps[$property] = $sch;
    }
    out += "var " + $errs + " = errors;";
    var $currentErrorPath = it2.errorPath;
    out += "var missing" + $lvl + ";";
    for (var $property in $propertyDeps) {
      $deps = $propertyDeps[$property];
      if ($deps.length) {
        out += " if ( " + $data + it2.util.getProperty($property) + " !== undefined ";
        if ($ownProperties) {
          out += " && Object.prototype.hasOwnProperty.call(" + $data + ", '" + it2.util.escapeQuotes($property) + "') ";
        }
        if ($breakOnError) {
          out += " && ( ";
          var arr1 = $deps;
          if (arr1) {
            var $propertyKey, $i = -1, l1 = arr1.length - 1;
            while ($i < l1) {
              $propertyKey = arr1[$i += 1];
              if ($i) {
                out += " || ";
              }
              var $prop = it2.util.getProperty($propertyKey), $useData = $data + $prop;
              out += " ( ( " + $useData + " === undefined ";
              if ($ownProperties) {
                out += " || ! Object.prototype.hasOwnProperty.call(" + $data + ", '" + it2.util.escapeQuotes($propertyKey) + "') ";
              }
              out += ") && (missing" + $lvl + " = " + it2.util.toQuotedString(it2.opts.jsonPointers ? $propertyKey : $prop) + ") ) ";
            }
          }
          out += ")) {  ";
          var $propertyPath = "missing" + $lvl, $missingProperty = "' + " + $propertyPath + " + '";
          if (it2.opts._errorDataPathProperty) {
            it2.errorPath = it2.opts.jsonPointers ? it2.util.getPathExpr($currentErrorPath, $propertyPath, true) : $currentErrorPath + " + " + $propertyPath;
          }
          var $$outStack = $$outStack || [];
          $$outStack.push(out);
          out = "";
          if (it2.createErrors !== false) {
            out += " { keyword: 'dependencies' , dataPath: (dataPath || '') + " + it2.errorPath + " , schemaPath: " + it2.util.toQuotedString($errSchemaPath) + " , params: { property: '" + it2.util.escapeQuotes($property) + "', missingProperty: '" + $missingProperty + "', depsCount: " + $deps.length + ", deps: '" + it2.util.escapeQuotes($deps.length == 1 ? $deps[0] : $deps.join(", ")) + "' } ";
            if (it2.opts.messages !== false) {
              out += " , message: 'should have ";
              if ($deps.length == 1) {
                out += "property " + it2.util.escapeQuotes($deps[0]);
              } else {
                out += "properties " + it2.util.escapeQuotes($deps.join(", "));
              }
              out += " when property " + it2.util.escapeQuotes($property) + " is present' ";
            }
            if (it2.opts.verbose) {
              out += " , schema: validate.schema" + $schemaPath + " , parentSchema: validate.schema" + it2.schemaPath + " , data: " + $data + " ";
            }
            out += " } ";
          } else {
            out += " {} ";
          }
          var __err = out;
          out = $$outStack.pop();
          if (!it2.compositeRule && $breakOnError) {
            if (it2.async) {
              out += " throw new ValidationError([" + __err + "]); ";
            } else {
              out += " validate.errors = [" + __err + "]; return false; ";
            }
          } else {
            out += " var err = " + __err + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ";
          }
        } else {
          out += " ) { ";
          var arr2 = $deps;
          if (arr2) {
            var $propertyKey, i2 = -1, l2 = arr2.length - 1;
            while (i2 < l2) {
              $propertyKey = arr2[i2 += 1];
              var $prop = it2.util.getProperty($propertyKey), $missingProperty = it2.util.escapeQuotes($propertyKey), $useData = $data + $prop;
              if (it2.opts._errorDataPathProperty) {
                it2.errorPath = it2.util.getPath($currentErrorPath, $propertyKey, it2.opts.jsonPointers);
              }
              out += " if ( " + $useData + " === undefined ";
              if ($ownProperties) {
                out += " || ! Object.prototype.hasOwnProperty.call(" + $data + ", '" + it2.util.escapeQuotes($propertyKey) + "') ";
              }
              out += ") {  var err =   ";
              if (it2.createErrors !== false) {
                out += " { keyword: 'dependencies' , dataPath: (dataPath || '') + " + it2.errorPath + " , schemaPath: " + it2.util.toQuotedString($errSchemaPath) + " , params: { property: '" + it2.util.escapeQuotes($property) + "', missingProperty: '" + $missingProperty + "', depsCount: " + $deps.length + ", deps: '" + it2.util.escapeQuotes($deps.length == 1 ? $deps[0] : $deps.join(", ")) + "' } ";
                if (it2.opts.messages !== false) {
                  out += " , message: 'should have ";
                  if ($deps.length == 1) {
                    out += "property " + it2.util.escapeQuotes($deps[0]);
                  } else {
                    out += "properties " + it2.util.escapeQuotes($deps.join(", "));
                  }
                  out += " when property " + it2.util.escapeQuotes($property) + " is present' ";
                }
                if (it2.opts.verbose) {
                  out += " , schema: validate.schema" + $schemaPath + " , parentSchema: validate.schema" + it2.schemaPath + " , data: " + $data + " ";
                }
                out += " } ";
              } else {
                out += " {} ";
              }
              out += ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; } ";
            }
          }
        }
        out += " }   ";
        if ($breakOnError) {
          $closingBraces += "}";
          out += " else { ";
        }
      }
    }
    it2.errorPath = $currentErrorPath;
    var $currentBaseId = $it.baseId;
    for (var $property in $schemaDeps) {
      var $sch = $schemaDeps[$property];
      if (it2.opts.strictKeywords ? typeof $sch == "object" && Object.keys($sch).length > 0 || $sch === false : it2.util.schemaHasRules($sch, it2.RULES.all)) {
        out += " " + $nextValid + " = true; if ( " + $data + it2.util.getProperty($property) + " !== undefined ";
        if ($ownProperties) {
          out += " && Object.prototype.hasOwnProperty.call(" + $data + ", '" + it2.util.escapeQuotes($property) + "') ";
        }
        out += ") { ";
        $it.schema = $sch;
        $it.schemaPath = $schemaPath + it2.util.getProperty($property);
        $it.errSchemaPath = $errSchemaPath + "/" + it2.util.escapeFragment($property);
        out += "  " + it2.validate($it) + " ";
        $it.baseId = $currentBaseId;
        out += " }  ";
        if ($breakOnError) {
          out += " if (" + $nextValid + ") { ";
          $closingBraces += "}";
        }
      }
    }
    if ($breakOnError) {
      out += "   " + $closingBraces + " if (" + $errs + " == errors) {";
    }
    return out;
  };
  return dependencies;
}
var _enum;
var hasRequired_enum;
function require_enum() {
  if (hasRequired_enum) return _enum;
  hasRequired_enum = 1;
  _enum = function generate_enum(it2, $keyword, $ruleType) {
    var out = " ";
    var $lvl = it2.level;
    var $dataLvl = it2.dataLevel;
    var $schema2 = it2.schema[$keyword];
    var $schemaPath = it2.schemaPath + it2.util.getProperty($keyword);
    var $errSchemaPath = it2.errSchemaPath + "/" + $keyword;
    var $breakOnError = !it2.opts.allErrors;
    var $data = "data" + ($dataLvl || "");
    var $valid = "valid" + $lvl;
    var $isData = it2.opts.$data && $schema2 && $schema2.$data;
    if ($isData) {
      out += " var schema" + $lvl + " = " + it2.util.getData($schema2.$data, $dataLvl, it2.dataPathArr) + "; ";
    }
    var $i = "i" + $lvl, $vSchema = "schema" + $lvl;
    if (!$isData) {
      out += " var " + $vSchema + " = validate.schema" + $schemaPath + ";";
    }
    out += "var " + $valid + ";";
    if ($isData) {
      out += " if (schema" + $lvl + " === undefined) " + $valid + " = true; else if (!Array.isArray(schema" + $lvl + ")) " + $valid + " = false; else {";
    }
    out += "" + $valid + " = false;for (var " + $i + "=0; " + $i + "<" + $vSchema + ".length; " + $i + "++) if (equal(" + $data + ", " + $vSchema + "[" + $i + "])) { " + $valid + " = true; break; }";
    if ($isData) {
      out += "  }  ";
    }
    out += " if (!" + $valid + ") {   ";
    var $$outStack = $$outStack || [];
    $$outStack.push(out);
    out = "";
    if (it2.createErrors !== false) {
      out += " { keyword: 'enum' , dataPath: (dataPath || '') + " + it2.errorPath + " , schemaPath: " + it2.util.toQuotedString($errSchemaPath) + " , params: { allowedValues: schema" + $lvl + " } ";
      if (it2.opts.messages !== false) {
        out += " , message: 'should be equal to one of the allowed values' ";
      }
      if (it2.opts.verbose) {
        out += " , schema: validate.schema" + $schemaPath + " , parentSchema: validate.schema" + it2.schemaPath + " , data: " + $data + " ";
      }
      out += " } ";
    } else {
      out += " {} ";
    }
    var __err = out;
    out = $$outStack.pop();
    if (!it2.compositeRule && $breakOnError) {
      if (it2.async) {
        out += " throw new ValidationError([" + __err + "]); ";
      } else {
        out += " validate.errors = [" + __err + "]; return false; ";
      }
    } else {
      out += " var err = " + __err + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ";
    }
    out += " }";
    if ($breakOnError) {
      out += " else { ";
    }
    return out;
  };
  return _enum;
}
var format;
var hasRequiredFormat;
function requireFormat() {
  if (hasRequiredFormat) return format;
  hasRequiredFormat = 1;
  format = function generate_format(it2, $keyword, $ruleType) {
    var out = " ";
    var $lvl = it2.level;
    var $dataLvl = it2.dataLevel;
    var $schema2 = it2.schema[$keyword];
    var $schemaPath = it2.schemaPath + it2.util.getProperty($keyword);
    var $errSchemaPath = it2.errSchemaPath + "/" + $keyword;
    var $breakOnError = !it2.opts.allErrors;
    var $data = "data" + ($dataLvl || "");
    if (it2.opts.format === false) {
      if ($breakOnError) {
        out += " if (true) { ";
      }
      return out;
    }
    var $isData = it2.opts.$data && $schema2 && $schema2.$data, $schemaValue;
    if ($isData) {
      out += " var schema" + $lvl + " = " + it2.util.getData($schema2.$data, $dataLvl, it2.dataPathArr) + "; ";
      $schemaValue = "schema" + $lvl;
    } else {
      $schemaValue = $schema2;
    }
    var $unknownFormats = it2.opts.unknownFormats, $allowUnknown = Array.isArray($unknownFormats);
    if ($isData) {
      var $format = "format" + $lvl, $isObject = "isObject" + $lvl, $formatType = "formatType" + $lvl;
      out += " var " + $format + " = formats[" + $schemaValue + "]; var " + $isObject + " = typeof " + $format + " == 'object' && !(" + $format + " instanceof RegExp) && " + $format + ".validate; var " + $formatType + " = " + $isObject + " && " + $format + ".type || 'string'; if (" + $isObject + ") { ";
      if (it2.async) {
        out += " var async" + $lvl + " = " + $format + ".async; ";
      }
      out += " " + $format + " = " + $format + ".validate; } if (  ";
      if ($isData) {
        out += " (" + $schemaValue + " !== undefined && typeof " + $schemaValue + " != 'string') || ";
      }
      out += " (";
      if ($unknownFormats != "ignore") {
        out += " (" + $schemaValue + " && !" + $format + " ";
        if ($allowUnknown) {
          out += " && self._opts.unknownFormats.indexOf(" + $schemaValue + ") == -1 ";
        }
        out += ") || ";
      }
      out += " (" + $format + " && " + $formatType + " == '" + $ruleType + "' && !(typeof " + $format + " == 'function' ? ";
      if (it2.async) {
        out += " (async" + $lvl + " ? await " + $format + "(" + $data + ") : " + $format + "(" + $data + ")) ";
      } else {
        out += " " + $format + "(" + $data + ") ";
      }
      out += " : " + $format + ".test(" + $data + "))))) {";
    } else {
      var $format = it2.formats[$schema2];
      if (!$format) {
        if ($unknownFormats == "ignore") {
          it2.logger.warn('unknown format "' + $schema2 + '" ignored in schema at path "' + it2.errSchemaPath + '"');
          if ($breakOnError) {
            out += " if (true) { ";
          }
          return out;
        } else if ($allowUnknown && $unknownFormats.indexOf($schema2) >= 0) {
          if ($breakOnError) {
            out += " if (true) { ";
          }
          return out;
        } else {
          throw new Error('unknown format "' + $schema2 + '" is used in schema at path "' + it2.errSchemaPath + '"');
        }
      }
      var $isObject = typeof $format == "object" && !($format instanceof RegExp) && $format.validate;
      var $formatType = $isObject && $format.type || "string";
      if ($isObject) {
        var $async = $format.async === true;
        $format = $format.validate;
      }
      if ($formatType != $ruleType) {
        if ($breakOnError) {
          out += " if (true) { ";
        }
        return out;
      }
      if ($async) {
        if (!it2.async) throw new Error("async format in sync schema");
        var $formatRef = "formats" + it2.util.getProperty($schema2) + ".validate";
        out += " if (!(await " + $formatRef + "(" + $data + "))) { ";
      } else {
        out += " if (! ";
        var $formatRef = "formats" + it2.util.getProperty($schema2);
        if ($isObject) $formatRef += ".validate";
        if (typeof $format == "function") {
          out += " " + $formatRef + "(" + $data + ") ";
        } else {
          out += " " + $formatRef + ".test(" + $data + ") ";
        }
        out += ") { ";
      }
    }
    var $$outStack = $$outStack || [];
    $$outStack.push(out);
    out = "";
    if (it2.createErrors !== false) {
      out += " { keyword: 'format' , dataPath: (dataPath || '') + " + it2.errorPath + " , schemaPath: " + it2.util.toQuotedString($errSchemaPath) + " , params: { format:  ";
      if ($isData) {
        out += "" + $schemaValue;
      } else {
        out += "" + it2.util.toQuotedString($schema2);
      }
      out += "  } ";
      if (it2.opts.messages !== false) {
        out += ` , message: 'should match format "`;
        if ($isData) {
          out += "' + " + $schemaValue + " + '";
        } else {
          out += "" + it2.util.escapeQuotes($schema2);
        }
        out += `"' `;
      }
      if (it2.opts.verbose) {
        out += " , schema:  ";
        if ($isData) {
          out += "validate.schema" + $schemaPath;
        } else {
          out += "" + it2.util.toQuotedString($schema2);
        }
        out += "         , parentSchema: validate.schema" + it2.schemaPath + " , data: " + $data + " ";
      }
      out += " } ";
    } else {
      out += " {} ";
    }
    var __err = out;
    out = $$outStack.pop();
    if (!it2.compositeRule && $breakOnError) {
      if (it2.async) {
        out += " throw new ValidationError([" + __err + "]); ";
      } else {
        out += " validate.errors = [" + __err + "]; return false; ";
      }
    } else {
      out += " var err = " + __err + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ";
    }
    out += " } ";
    if ($breakOnError) {
      out += " else { ";
    }
    return out;
  };
  return format;
}
var _if;
var hasRequired_if;
function require_if() {
  if (hasRequired_if) return _if;
  hasRequired_if = 1;
  _if = function generate_if(it2, $keyword, $ruleType) {
    var out = " ";
    var $lvl = it2.level;
    var $dataLvl = it2.dataLevel;
    var $schema2 = it2.schema[$keyword];
    var $schemaPath = it2.schemaPath + it2.util.getProperty($keyword);
    var $errSchemaPath = it2.errSchemaPath + "/" + $keyword;
    var $breakOnError = !it2.opts.allErrors;
    var $data = "data" + ($dataLvl || "");
    var $valid = "valid" + $lvl;
    var $errs = "errs__" + $lvl;
    var $it = it2.util.copy(it2);
    $it.level++;
    var $nextValid = "valid" + $it.level;
    var $thenSch = it2.schema["then"], $elseSch = it2.schema["else"], $thenPresent = $thenSch !== void 0 && (it2.opts.strictKeywords ? typeof $thenSch == "object" && Object.keys($thenSch).length > 0 || $thenSch === false : it2.util.schemaHasRules($thenSch, it2.RULES.all)), $elsePresent = $elseSch !== void 0 && (it2.opts.strictKeywords ? typeof $elseSch == "object" && Object.keys($elseSch).length > 0 || $elseSch === false : it2.util.schemaHasRules($elseSch, it2.RULES.all)), $currentBaseId = $it.baseId;
    if ($thenPresent || $elsePresent) {
      var $ifClause;
      $it.createErrors = false;
      $it.schema = $schema2;
      $it.schemaPath = $schemaPath;
      $it.errSchemaPath = $errSchemaPath;
      out += " var " + $errs + " = errors; var " + $valid + " = true;  ";
      var $wasComposite = it2.compositeRule;
      it2.compositeRule = $it.compositeRule = true;
      out += "  " + it2.validate($it) + " ";
      $it.baseId = $currentBaseId;
      $it.createErrors = true;
      out += "  errors = " + $errs + "; if (vErrors !== null) { if (" + $errs + ") vErrors.length = " + $errs + "; else vErrors = null; }  ";
      it2.compositeRule = $it.compositeRule = $wasComposite;
      if ($thenPresent) {
        out += " if (" + $nextValid + ") {  ";
        $it.schema = it2.schema["then"];
        $it.schemaPath = it2.schemaPath + ".then";
        $it.errSchemaPath = it2.errSchemaPath + "/then";
        out += "  " + it2.validate($it) + " ";
        $it.baseId = $currentBaseId;
        out += " " + $valid + " = " + $nextValid + "; ";
        if ($thenPresent && $elsePresent) {
          $ifClause = "ifClause" + $lvl;
          out += " var " + $ifClause + " = 'then'; ";
        } else {
          $ifClause = "'then'";
        }
        out += " } ";
        if ($elsePresent) {
          out += " else { ";
        }
      } else {
        out += " if (!" + $nextValid + ") { ";
      }
      if ($elsePresent) {
        $it.schema = it2.schema["else"];
        $it.schemaPath = it2.schemaPath + ".else";
        $it.errSchemaPath = it2.errSchemaPath + "/else";
        out += "  " + it2.validate($it) + " ";
        $it.baseId = $currentBaseId;
        out += " " + $valid + " = " + $nextValid + "; ";
        if ($thenPresent && $elsePresent) {
          $ifClause = "ifClause" + $lvl;
          out += " var " + $ifClause + " = 'else'; ";
        } else {
          $ifClause = "'else'";
        }
        out += " } ";
      }
      out += " if (!" + $valid + ") {   var err =   ";
      if (it2.createErrors !== false) {
        out += " { keyword: 'if' , dataPath: (dataPath || '') + " + it2.errorPath + " , schemaPath: " + it2.util.toQuotedString($errSchemaPath) + " , params: { failingKeyword: " + $ifClause + " } ";
        if (it2.opts.messages !== false) {
          out += ` , message: 'should match "' + ` + $ifClause + ` + '" schema' `;
        }
        if (it2.opts.verbose) {
          out += " , schema: validate.schema" + $schemaPath + " , parentSchema: validate.schema" + it2.schemaPath + " , data: " + $data + " ";
        }
        out += " } ";
      } else {
        out += " {} ";
      }
      out += ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ";
      if (!it2.compositeRule && $breakOnError) {
        if (it2.async) {
          out += " throw new ValidationError(vErrors); ";
        } else {
          out += " validate.errors = vErrors; return false; ";
        }
      }
      out += " }   ";
      if ($breakOnError) {
        out += " else { ";
      }
    } else {
      if ($breakOnError) {
        out += " if (true) { ";
      }
    }
    return out;
  };
  return _if;
}
var items;
var hasRequiredItems;
function requireItems() {
  if (hasRequiredItems) return items;
  hasRequiredItems = 1;
  items = function generate_items(it2, $keyword, $ruleType) {
    var out = " ";
    var $lvl = it2.level;
    var $dataLvl = it2.dataLevel;
    var $schema2 = it2.schema[$keyword];
    var $schemaPath = it2.schemaPath + it2.util.getProperty($keyword);
    var $errSchemaPath = it2.errSchemaPath + "/" + $keyword;
    var $breakOnError = !it2.opts.allErrors;
    var $data = "data" + ($dataLvl || "");
    var $valid = "valid" + $lvl;
    var $errs = "errs__" + $lvl;
    var $it = it2.util.copy(it2);
    var $closingBraces = "";
    $it.level++;
    var $nextValid = "valid" + $it.level;
    var $idx = "i" + $lvl, $dataNxt = $it.dataLevel = it2.dataLevel + 1, $nextData = "data" + $dataNxt, $currentBaseId = it2.baseId;
    out += "var " + $errs + " = errors;var " + $valid + ";";
    if (Array.isArray($schema2)) {
      var $additionalItems = it2.schema.additionalItems;
      if ($additionalItems === false) {
        out += " " + $valid + " = " + $data + ".length <= " + $schema2.length + "; ";
        var $currErrSchemaPath = $errSchemaPath;
        $errSchemaPath = it2.errSchemaPath + "/additionalItems";
        out += "  if (!" + $valid + ") {   ";
        var $$outStack = $$outStack || [];
        $$outStack.push(out);
        out = "";
        if (it2.createErrors !== false) {
          out += " { keyword: 'additionalItems' , dataPath: (dataPath || '') + " + it2.errorPath + " , schemaPath: " + it2.util.toQuotedString($errSchemaPath) + " , params: { limit: " + $schema2.length + " } ";
          if (it2.opts.messages !== false) {
            out += " , message: 'should NOT have more than " + $schema2.length + " items' ";
          }
          if (it2.opts.verbose) {
            out += " , schema: false , parentSchema: validate.schema" + it2.schemaPath + " , data: " + $data + " ";
          }
          out += " } ";
        } else {
          out += " {} ";
        }
        var __err = out;
        out = $$outStack.pop();
        if (!it2.compositeRule && $breakOnError) {
          if (it2.async) {
            out += " throw new ValidationError([" + __err + "]); ";
          } else {
            out += " validate.errors = [" + __err + "]; return false; ";
          }
        } else {
          out += " var err = " + __err + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ";
        }
        out += " } ";
        $errSchemaPath = $currErrSchemaPath;
        if ($breakOnError) {
          $closingBraces += "}";
          out += " else { ";
        }
      }
      var arr1 = $schema2;
      if (arr1) {
        var $sch, $i = -1, l1 = arr1.length - 1;
        while ($i < l1) {
          $sch = arr1[$i += 1];
          if (it2.opts.strictKeywords ? typeof $sch == "object" && Object.keys($sch).length > 0 || $sch === false : it2.util.schemaHasRules($sch, it2.RULES.all)) {
            out += " " + $nextValid + " = true; if (" + $data + ".length > " + $i + ") { ";
            var $passData = $data + "[" + $i + "]";
            $it.schema = $sch;
            $it.schemaPath = $schemaPath + "[" + $i + "]";
            $it.errSchemaPath = $errSchemaPath + "/" + $i;
            $it.errorPath = it2.util.getPathExpr(it2.errorPath, $i, it2.opts.jsonPointers, true);
            $it.dataPathArr[$dataNxt] = $i;
            var $code = it2.validate($it);
            $it.baseId = $currentBaseId;
            if (it2.util.varOccurences($code, $nextData) < 2) {
              out += " " + it2.util.varReplace($code, $nextData, $passData) + " ";
            } else {
              out += " var " + $nextData + " = " + $passData + "; " + $code + " ";
            }
            out += " }  ";
            if ($breakOnError) {
              out += " if (" + $nextValid + ") { ";
              $closingBraces += "}";
            }
          }
        }
      }
      if (typeof $additionalItems == "object" && (it2.opts.strictKeywords ? typeof $additionalItems == "object" && Object.keys($additionalItems).length > 0 || $additionalItems === false : it2.util.schemaHasRules($additionalItems, it2.RULES.all))) {
        $it.schema = $additionalItems;
        $it.schemaPath = it2.schemaPath + ".additionalItems";
        $it.errSchemaPath = it2.errSchemaPath + "/additionalItems";
        out += " " + $nextValid + " = true; if (" + $data + ".length > " + $schema2.length + ") {  for (var " + $idx + " = " + $schema2.length + "; " + $idx + " < " + $data + ".length; " + $idx + "++) { ";
        $it.errorPath = it2.util.getPathExpr(it2.errorPath, $idx, it2.opts.jsonPointers, true);
        var $passData = $data + "[" + $idx + "]";
        $it.dataPathArr[$dataNxt] = $idx;
        var $code = it2.validate($it);
        $it.baseId = $currentBaseId;
        if (it2.util.varOccurences($code, $nextData) < 2) {
          out += " " + it2.util.varReplace($code, $nextData, $passData) + " ";
        } else {
          out += " var " + $nextData + " = " + $passData + "; " + $code + " ";
        }
        if ($breakOnError) {
          out += " if (!" + $nextValid + ") break; ";
        }
        out += " } }  ";
        if ($breakOnError) {
          out += " if (" + $nextValid + ") { ";
          $closingBraces += "}";
        }
      }
    } else if (it2.opts.strictKeywords ? typeof $schema2 == "object" && Object.keys($schema2).length > 0 || $schema2 === false : it2.util.schemaHasRules($schema2, it2.RULES.all)) {
      $it.schema = $schema2;
      $it.schemaPath = $schemaPath;
      $it.errSchemaPath = $errSchemaPath;
      out += "  for (var " + $idx + " = 0; " + $idx + " < " + $data + ".length; " + $idx + "++) { ";
      $it.errorPath = it2.util.getPathExpr(it2.errorPath, $idx, it2.opts.jsonPointers, true);
      var $passData = $data + "[" + $idx + "]";
      $it.dataPathArr[$dataNxt] = $idx;
      var $code = it2.validate($it);
      $it.baseId = $currentBaseId;
      if (it2.util.varOccurences($code, $nextData) < 2) {
        out += " " + it2.util.varReplace($code, $nextData, $passData) + " ";
      } else {
        out += " var " + $nextData + " = " + $passData + "; " + $code + " ";
      }
      if ($breakOnError) {
        out += " if (!" + $nextValid + ") break; ";
      }
      out += " }";
    }
    if ($breakOnError) {
      out += " " + $closingBraces + " if (" + $errs + " == errors) {";
    }
    return out;
  };
  return items;
}
var _limit;
var hasRequired_limit;
function require_limit() {
  if (hasRequired_limit) return _limit;
  hasRequired_limit = 1;
  _limit = function generate__limit(it2, $keyword, $ruleType) {
    var out = " ";
    var $lvl = it2.level;
    var $dataLvl = it2.dataLevel;
    var $schema2 = it2.schema[$keyword];
    var $schemaPath = it2.schemaPath + it2.util.getProperty($keyword);
    var $errSchemaPath = it2.errSchemaPath + "/" + $keyword;
    var $breakOnError = !it2.opts.allErrors;
    var $errorKeyword;
    var $data = "data" + ($dataLvl || "");
    var $isData = it2.opts.$data && $schema2 && $schema2.$data, $schemaValue;
    if ($isData) {
      out += " var schema" + $lvl + " = " + it2.util.getData($schema2.$data, $dataLvl, it2.dataPathArr) + "; ";
      $schemaValue = "schema" + $lvl;
    } else {
      $schemaValue = $schema2;
    }
    var $isMax = $keyword == "maximum", $exclusiveKeyword = $isMax ? "exclusiveMaximum" : "exclusiveMinimum", $schemaExcl = it2.schema[$exclusiveKeyword], $isDataExcl = it2.opts.$data && $schemaExcl && $schemaExcl.$data, $op = $isMax ? "<" : ">", $notOp = $isMax ? ">" : "<", $errorKeyword = void 0;
    if (!($isData || typeof $schema2 == "number" || $schema2 === void 0)) {
      throw new Error($keyword + " must be number");
    }
    if (!($isDataExcl || $schemaExcl === void 0 || typeof $schemaExcl == "number" || typeof $schemaExcl == "boolean")) {
      throw new Error($exclusiveKeyword + " must be number or boolean");
    }
    if ($isDataExcl) {
      var $schemaValueExcl = it2.util.getData($schemaExcl.$data, $dataLvl, it2.dataPathArr), $exclusive = "exclusive" + $lvl, $exclType = "exclType" + $lvl, $exclIsNumber = "exclIsNumber" + $lvl, $opExpr = "op" + $lvl, $opStr = "' + " + $opExpr + " + '";
      out += " var schemaExcl" + $lvl + " = " + $schemaValueExcl + "; ";
      $schemaValueExcl = "schemaExcl" + $lvl;
      out += " var " + $exclusive + "; var " + $exclType + " = typeof " + $schemaValueExcl + "; if (" + $exclType + " != 'boolean' && " + $exclType + " != 'undefined' && " + $exclType + " != 'number') { ";
      var $errorKeyword = $exclusiveKeyword;
      var $$outStack = $$outStack || [];
      $$outStack.push(out);
      out = "";
      if (it2.createErrors !== false) {
        out += " { keyword: '" + ($errorKeyword || "_exclusiveLimit") + "' , dataPath: (dataPath || '') + " + it2.errorPath + " , schemaPath: " + it2.util.toQuotedString($errSchemaPath) + " , params: {} ";
        if (it2.opts.messages !== false) {
          out += " , message: '" + $exclusiveKeyword + " should be boolean' ";
        }
        if (it2.opts.verbose) {
          out += " , schema: validate.schema" + $schemaPath + " , parentSchema: validate.schema" + it2.schemaPath + " , data: " + $data + " ";
        }
        out += " } ";
      } else {
        out += " {} ";
      }
      var __err = out;
      out = $$outStack.pop();
      if (!it2.compositeRule && $breakOnError) {
        if (it2.async) {
          out += " throw new ValidationError([" + __err + "]); ";
        } else {
          out += " validate.errors = [" + __err + "]; return false; ";
        }
      } else {
        out += " var err = " + __err + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ";
      }
      out += " } else if ( ";
      if ($isData) {
        out += " (" + $schemaValue + " !== undefined && typeof " + $schemaValue + " != 'number') || ";
      }
      out += " " + $exclType + " == 'number' ? ( (" + $exclusive + " = " + $schemaValue + " === undefined || " + $schemaValueExcl + " " + $op + "= " + $schemaValue + ") ? " + $data + " " + $notOp + "= " + $schemaValueExcl + " : " + $data + " " + $notOp + " " + $schemaValue + " ) : ( (" + $exclusive + " = " + $schemaValueExcl + " === true) ? " + $data + " " + $notOp + "= " + $schemaValue + " : " + $data + " " + $notOp + " " + $schemaValue + " ) || " + $data + " !== " + $data + ") { var op" + $lvl + " = " + $exclusive + " ? '" + $op + "' : '" + $op + "='; ";
      if ($schema2 === void 0) {
        $errorKeyword = $exclusiveKeyword;
        $errSchemaPath = it2.errSchemaPath + "/" + $exclusiveKeyword;
        $schemaValue = $schemaValueExcl;
        $isData = $isDataExcl;
      }
    } else {
      var $exclIsNumber = typeof $schemaExcl == "number", $opStr = $op;
      if ($exclIsNumber && $isData) {
        var $opExpr = "'" + $opStr + "'";
        out += " if ( ";
        if ($isData) {
          out += " (" + $schemaValue + " !== undefined && typeof " + $schemaValue + " != 'number') || ";
        }
        out += " ( " + $schemaValue + " === undefined || " + $schemaExcl + " " + $op + "= " + $schemaValue + " ? " + $data + " " + $notOp + "= " + $schemaExcl + " : " + $data + " " + $notOp + " " + $schemaValue + " ) || " + $data + " !== " + $data + ") { ";
      } else {
        if ($exclIsNumber && $schema2 === void 0) {
          $exclusive = true;
          $errorKeyword = $exclusiveKeyword;
          $errSchemaPath = it2.errSchemaPath + "/" + $exclusiveKeyword;
          $schemaValue = $schemaExcl;
          $notOp += "=";
        } else {
          if ($exclIsNumber) $schemaValue = Math[$isMax ? "min" : "max"]($schemaExcl, $schema2);
          if ($schemaExcl === ($exclIsNumber ? $schemaValue : true)) {
            $exclusive = true;
            $errorKeyword = $exclusiveKeyword;
            $errSchemaPath = it2.errSchemaPath + "/" + $exclusiveKeyword;
            $notOp += "=";
          } else {
            $exclusive = false;
            $opStr += "=";
          }
        }
        var $opExpr = "'" + $opStr + "'";
        out += " if ( ";
        if ($isData) {
          out += " (" + $schemaValue + " !== undefined && typeof " + $schemaValue + " != 'number') || ";
        }
        out += " " + $data + " " + $notOp + " " + $schemaValue + " || " + $data + " !== " + $data + ") { ";
      }
    }
    $errorKeyword = $errorKeyword || $keyword;
    var $$outStack = $$outStack || [];
    $$outStack.push(out);
    out = "";
    if (it2.createErrors !== false) {
      out += " { keyword: '" + ($errorKeyword || "_limit") + "' , dataPath: (dataPath || '') + " + it2.errorPath + " , schemaPath: " + it2.util.toQuotedString($errSchemaPath) + " , params: { comparison: " + $opExpr + ", limit: " + $schemaValue + ", exclusive: " + $exclusive + " } ";
      if (it2.opts.messages !== false) {
        out += " , message: 'should be " + $opStr + " ";
        if ($isData) {
          out += "' + " + $schemaValue;
        } else {
          out += "" + $schemaValue + "'";
        }
      }
      if (it2.opts.verbose) {
        out += " , schema:  ";
        if ($isData) {
          out += "validate.schema" + $schemaPath;
        } else {
          out += "" + $schema2;
        }
        out += "         , parentSchema: validate.schema" + it2.schemaPath + " , data: " + $data + " ";
      }
      out += " } ";
    } else {
      out += " {} ";
    }
    var __err = out;
    out = $$outStack.pop();
    if (!it2.compositeRule && $breakOnError) {
      if (it2.async) {
        out += " throw new ValidationError([" + __err + "]); ";
      } else {
        out += " validate.errors = [" + __err + "]; return false; ";
      }
    } else {
      out += " var err = " + __err + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ";
    }
    out += " } ";
    if ($breakOnError) {
      out += " else { ";
    }
    return out;
  };
  return _limit;
}
var _limitItems;
var hasRequired_limitItems;
function require_limitItems() {
  if (hasRequired_limitItems) return _limitItems;
  hasRequired_limitItems = 1;
  _limitItems = function generate__limitItems(it2, $keyword, $ruleType) {
    var out = " ";
    var $lvl = it2.level;
    var $dataLvl = it2.dataLevel;
    var $schema2 = it2.schema[$keyword];
    var $schemaPath = it2.schemaPath + it2.util.getProperty($keyword);
    var $errSchemaPath = it2.errSchemaPath + "/" + $keyword;
    var $breakOnError = !it2.opts.allErrors;
    var $errorKeyword;
    var $data = "data" + ($dataLvl || "");
    var $isData = it2.opts.$data && $schema2 && $schema2.$data, $schemaValue;
    if ($isData) {
      out += " var schema" + $lvl + " = " + it2.util.getData($schema2.$data, $dataLvl, it2.dataPathArr) + "; ";
      $schemaValue = "schema" + $lvl;
    } else {
      $schemaValue = $schema2;
    }
    if (!($isData || typeof $schema2 == "number")) {
      throw new Error($keyword + " must be number");
    }
    var $op = $keyword == "maxItems" ? ">" : "<";
    out += "if ( ";
    if ($isData) {
      out += " (" + $schemaValue + " !== undefined && typeof " + $schemaValue + " != 'number') || ";
    }
    out += " " + $data + ".length " + $op + " " + $schemaValue + ") { ";
    var $errorKeyword = $keyword;
    var $$outStack = $$outStack || [];
    $$outStack.push(out);
    out = "";
    if (it2.createErrors !== false) {
      out += " { keyword: '" + ($errorKeyword || "_limitItems") + "' , dataPath: (dataPath || '') + " + it2.errorPath + " , schemaPath: " + it2.util.toQuotedString($errSchemaPath) + " , params: { limit: " + $schemaValue + " } ";
      if (it2.opts.messages !== false) {
        out += " , message: 'should NOT have ";
        if ($keyword == "maxItems") {
          out += "more";
        } else {
          out += "fewer";
        }
        out += " than ";
        if ($isData) {
          out += "' + " + $schemaValue + " + '";
        } else {
          out += "" + $schema2;
        }
        out += " items' ";
      }
      if (it2.opts.verbose) {
        out += " , schema:  ";
        if ($isData) {
          out += "validate.schema" + $schemaPath;
        } else {
          out += "" + $schema2;
        }
        out += "         , parentSchema: validate.schema" + it2.schemaPath + " , data: " + $data + " ";
      }
      out += " } ";
    } else {
      out += " {} ";
    }
    var __err = out;
    out = $$outStack.pop();
    if (!it2.compositeRule && $breakOnError) {
      if (it2.async) {
        out += " throw new ValidationError([" + __err + "]); ";
      } else {
        out += " validate.errors = [" + __err + "]; return false; ";
      }
    } else {
      out += " var err = " + __err + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ";
    }
    out += "} ";
    if ($breakOnError) {
      out += " else { ";
    }
    return out;
  };
  return _limitItems;
}
var _limitLength;
var hasRequired_limitLength;
function require_limitLength() {
  if (hasRequired_limitLength) return _limitLength;
  hasRequired_limitLength = 1;
  _limitLength = function generate__limitLength(it2, $keyword, $ruleType) {
    var out = " ";
    var $lvl = it2.level;
    var $dataLvl = it2.dataLevel;
    var $schema2 = it2.schema[$keyword];
    var $schemaPath = it2.schemaPath + it2.util.getProperty($keyword);
    var $errSchemaPath = it2.errSchemaPath + "/" + $keyword;
    var $breakOnError = !it2.opts.allErrors;
    var $errorKeyword;
    var $data = "data" + ($dataLvl || "");
    var $isData = it2.opts.$data && $schema2 && $schema2.$data, $schemaValue;
    if ($isData) {
      out += " var schema" + $lvl + " = " + it2.util.getData($schema2.$data, $dataLvl, it2.dataPathArr) + "; ";
      $schemaValue = "schema" + $lvl;
    } else {
      $schemaValue = $schema2;
    }
    if (!($isData || typeof $schema2 == "number")) {
      throw new Error($keyword + " must be number");
    }
    var $op = $keyword == "maxLength" ? ">" : "<";
    out += "if ( ";
    if ($isData) {
      out += " (" + $schemaValue + " !== undefined && typeof " + $schemaValue + " != 'number') || ";
    }
    if (it2.opts.unicode === false) {
      out += " " + $data + ".length ";
    } else {
      out += " ucs2length(" + $data + ") ";
    }
    out += " " + $op + " " + $schemaValue + ") { ";
    var $errorKeyword = $keyword;
    var $$outStack = $$outStack || [];
    $$outStack.push(out);
    out = "";
    if (it2.createErrors !== false) {
      out += " { keyword: '" + ($errorKeyword || "_limitLength") + "' , dataPath: (dataPath || '') + " + it2.errorPath + " , schemaPath: " + it2.util.toQuotedString($errSchemaPath) + " , params: { limit: " + $schemaValue + " } ";
      if (it2.opts.messages !== false) {
        out += " , message: 'should NOT be ";
        if ($keyword == "maxLength") {
          out += "longer";
        } else {
          out += "shorter";
        }
        out += " than ";
        if ($isData) {
          out += "' + " + $schemaValue + " + '";
        } else {
          out += "" + $schema2;
        }
        out += " characters' ";
      }
      if (it2.opts.verbose) {
        out += " , schema:  ";
        if ($isData) {
          out += "validate.schema" + $schemaPath;
        } else {
          out += "" + $schema2;
        }
        out += "         , parentSchema: validate.schema" + it2.schemaPath + " , data: " + $data + " ";
      }
      out += " } ";
    } else {
      out += " {} ";
    }
    var __err = out;
    out = $$outStack.pop();
    if (!it2.compositeRule && $breakOnError) {
      if (it2.async) {
        out += " throw new ValidationError([" + __err + "]); ";
      } else {
        out += " validate.errors = [" + __err + "]; return false; ";
      }
    } else {
      out += " var err = " + __err + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ";
    }
    out += "} ";
    if ($breakOnError) {
      out += " else { ";
    }
    return out;
  };
  return _limitLength;
}
var _limitProperties;
var hasRequired_limitProperties;
function require_limitProperties() {
  if (hasRequired_limitProperties) return _limitProperties;
  hasRequired_limitProperties = 1;
  _limitProperties = function generate__limitProperties(it2, $keyword, $ruleType) {
    var out = " ";
    var $lvl = it2.level;
    var $dataLvl = it2.dataLevel;
    var $schema2 = it2.schema[$keyword];
    var $schemaPath = it2.schemaPath + it2.util.getProperty($keyword);
    var $errSchemaPath = it2.errSchemaPath + "/" + $keyword;
    var $breakOnError = !it2.opts.allErrors;
    var $errorKeyword;
    var $data = "data" + ($dataLvl || "");
    var $isData = it2.opts.$data && $schema2 && $schema2.$data, $schemaValue;
    if ($isData) {
      out += " var schema" + $lvl + " = " + it2.util.getData($schema2.$data, $dataLvl, it2.dataPathArr) + "; ";
      $schemaValue = "schema" + $lvl;
    } else {
      $schemaValue = $schema2;
    }
    if (!($isData || typeof $schema2 == "number")) {
      throw new Error($keyword + " must be number");
    }
    var $op = $keyword == "maxProperties" ? ">" : "<";
    out += "if ( ";
    if ($isData) {
      out += " (" + $schemaValue + " !== undefined && typeof " + $schemaValue + " != 'number') || ";
    }
    out += " Object.keys(" + $data + ").length " + $op + " " + $schemaValue + ") { ";
    var $errorKeyword = $keyword;
    var $$outStack = $$outStack || [];
    $$outStack.push(out);
    out = "";
    if (it2.createErrors !== false) {
      out += " { keyword: '" + ($errorKeyword || "_limitProperties") + "' , dataPath: (dataPath || '') + " + it2.errorPath + " , schemaPath: " + it2.util.toQuotedString($errSchemaPath) + " , params: { limit: " + $schemaValue + " } ";
      if (it2.opts.messages !== false) {
        out += " , message: 'should NOT have ";
        if ($keyword == "maxProperties") {
          out += "more";
        } else {
          out += "fewer";
        }
        out += " than ";
        if ($isData) {
          out += "' + " + $schemaValue + " + '";
        } else {
          out += "" + $schema2;
        }
        out += " properties' ";
      }
      if (it2.opts.verbose) {
        out += " , schema:  ";
        if ($isData) {
          out += "validate.schema" + $schemaPath;
        } else {
          out += "" + $schema2;
        }
        out += "         , parentSchema: validate.schema" + it2.schemaPath + " , data: " + $data + " ";
      }
      out += " } ";
    } else {
      out += " {} ";
    }
    var __err = out;
    out = $$outStack.pop();
    if (!it2.compositeRule && $breakOnError) {
      if (it2.async) {
        out += " throw new ValidationError([" + __err + "]); ";
      } else {
        out += " validate.errors = [" + __err + "]; return false; ";
      }
    } else {
      out += " var err = " + __err + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ";
    }
    out += "} ";
    if ($breakOnError) {
      out += " else { ";
    }
    return out;
  };
  return _limitProperties;
}
var multipleOf;
var hasRequiredMultipleOf;
function requireMultipleOf() {
  if (hasRequiredMultipleOf) return multipleOf;
  hasRequiredMultipleOf = 1;
  multipleOf = function generate_multipleOf(it2, $keyword, $ruleType) {
    var out = " ";
    var $lvl = it2.level;
    var $dataLvl = it2.dataLevel;
    var $schema2 = it2.schema[$keyword];
    var $schemaPath = it2.schemaPath + it2.util.getProperty($keyword);
    var $errSchemaPath = it2.errSchemaPath + "/" + $keyword;
    var $breakOnError = !it2.opts.allErrors;
    var $data = "data" + ($dataLvl || "");
    var $isData = it2.opts.$data && $schema2 && $schema2.$data, $schemaValue;
    if ($isData) {
      out += " var schema" + $lvl + " = " + it2.util.getData($schema2.$data, $dataLvl, it2.dataPathArr) + "; ";
      $schemaValue = "schema" + $lvl;
    } else {
      $schemaValue = $schema2;
    }
    if (!($isData || typeof $schema2 == "number")) {
      throw new Error($keyword + " must be number");
    }
    out += "var division" + $lvl + ";if (";
    if ($isData) {
      out += " " + $schemaValue + " !== undefined && ( typeof " + $schemaValue + " != 'number' || ";
    }
    out += " (division" + $lvl + " = " + $data + " / " + $schemaValue + ", ";
    if (it2.opts.multipleOfPrecision) {
      out += " Math.abs(Math.round(division" + $lvl + ") - division" + $lvl + ") > 1e-" + it2.opts.multipleOfPrecision + " ";
    } else {
      out += " division" + $lvl + " !== parseInt(division" + $lvl + ") ";
    }
    out += " ) ";
    if ($isData) {
      out += "  )  ";
    }
    out += " ) {   ";
    var $$outStack = $$outStack || [];
    $$outStack.push(out);
    out = "";
    if (it2.createErrors !== false) {
      out += " { keyword: 'multipleOf' , dataPath: (dataPath || '') + " + it2.errorPath + " , schemaPath: " + it2.util.toQuotedString($errSchemaPath) + " , params: { multipleOf: " + $schemaValue + " } ";
      if (it2.opts.messages !== false) {
        out += " , message: 'should be multiple of ";
        if ($isData) {
          out += "' + " + $schemaValue;
        } else {
          out += "" + $schemaValue + "'";
        }
      }
      if (it2.opts.verbose) {
        out += " , schema:  ";
        if ($isData) {
          out += "validate.schema" + $schemaPath;
        } else {
          out += "" + $schema2;
        }
        out += "         , parentSchema: validate.schema" + it2.schemaPath + " , data: " + $data + " ";
      }
      out += " } ";
    } else {
      out += " {} ";
    }
    var __err = out;
    out = $$outStack.pop();
    if (!it2.compositeRule && $breakOnError) {
      if (it2.async) {
        out += " throw new ValidationError([" + __err + "]); ";
      } else {
        out += " validate.errors = [" + __err + "]; return false; ";
      }
    } else {
      out += " var err = " + __err + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ";
    }
    out += "} ";
    if ($breakOnError) {
      out += " else { ";
    }
    return out;
  };
  return multipleOf;
}
var not;
var hasRequiredNot;
function requireNot() {
  if (hasRequiredNot) return not;
  hasRequiredNot = 1;
  not = function generate_not(it2, $keyword, $ruleType) {
    var out = " ";
    var $lvl = it2.level;
    var $dataLvl = it2.dataLevel;
    var $schema2 = it2.schema[$keyword];
    var $schemaPath = it2.schemaPath + it2.util.getProperty($keyword);
    var $errSchemaPath = it2.errSchemaPath + "/" + $keyword;
    var $breakOnError = !it2.opts.allErrors;
    var $data = "data" + ($dataLvl || "");
    var $errs = "errs__" + $lvl;
    var $it = it2.util.copy(it2);
    $it.level++;
    var $nextValid = "valid" + $it.level;
    if (it2.opts.strictKeywords ? typeof $schema2 == "object" && Object.keys($schema2).length > 0 || $schema2 === false : it2.util.schemaHasRules($schema2, it2.RULES.all)) {
      $it.schema = $schema2;
      $it.schemaPath = $schemaPath;
      $it.errSchemaPath = $errSchemaPath;
      out += " var " + $errs + " = errors;  ";
      var $wasComposite = it2.compositeRule;
      it2.compositeRule = $it.compositeRule = true;
      $it.createErrors = false;
      var $allErrorsOption;
      if ($it.opts.allErrors) {
        $allErrorsOption = $it.opts.allErrors;
        $it.opts.allErrors = false;
      }
      out += " " + it2.validate($it) + " ";
      $it.createErrors = true;
      if ($allErrorsOption) $it.opts.allErrors = $allErrorsOption;
      it2.compositeRule = $it.compositeRule = $wasComposite;
      out += " if (" + $nextValid + ") {   ";
      var $$outStack = $$outStack || [];
      $$outStack.push(out);
      out = "";
      if (it2.createErrors !== false) {
        out += " { keyword: 'not' , dataPath: (dataPath || '') + " + it2.errorPath + " , schemaPath: " + it2.util.toQuotedString($errSchemaPath) + " , params: {} ";
        if (it2.opts.messages !== false) {
          out += " , message: 'should NOT be valid' ";
        }
        if (it2.opts.verbose) {
          out += " , schema: validate.schema" + $schemaPath + " , parentSchema: validate.schema" + it2.schemaPath + " , data: " + $data + " ";
        }
        out += " } ";
      } else {
        out += " {} ";
      }
      var __err = out;
      out = $$outStack.pop();
      if (!it2.compositeRule && $breakOnError) {
        if (it2.async) {
          out += " throw new ValidationError([" + __err + "]); ";
        } else {
          out += " validate.errors = [" + __err + "]; return false; ";
        }
      } else {
        out += " var err = " + __err + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ";
      }
      out += " } else {  errors = " + $errs + "; if (vErrors !== null) { if (" + $errs + ") vErrors.length = " + $errs + "; else vErrors = null; } ";
      if (it2.opts.allErrors) {
        out += " } ";
      }
    } else {
      out += "  var err =   ";
      if (it2.createErrors !== false) {
        out += " { keyword: 'not' , dataPath: (dataPath || '') + " + it2.errorPath + " , schemaPath: " + it2.util.toQuotedString($errSchemaPath) + " , params: {} ";
        if (it2.opts.messages !== false) {
          out += " , message: 'should NOT be valid' ";
        }
        if (it2.opts.verbose) {
          out += " , schema: validate.schema" + $schemaPath + " , parentSchema: validate.schema" + it2.schemaPath + " , data: " + $data + " ";
        }
        out += " } ";
      } else {
        out += " {} ";
      }
      out += ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ";
      if ($breakOnError) {
        out += " if (false) { ";
      }
    }
    return out;
  };
  return not;
}
var oneOf;
var hasRequiredOneOf;
function requireOneOf() {
  if (hasRequiredOneOf) return oneOf;
  hasRequiredOneOf = 1;
  oneOf = function generate_oneOf(it2, $keyword, $ruleType) {
    var out = " ";
    var $lvl = it2.level;
    var $dataLvl = it2.dataLevel;
    var $schema2 = it2.schema[$keyword];
    var $schemaPath = it2.schemaPath + it2.util.getProperty($keyword);
    var $errSchemaPath = it2.errSchemaPath + "/" + $keyword;
    var $breakOnError = !it2.opts.allErrors;
    var $data = "data" + ($dataLvl || "");
    var $valid = "valid" + $lvl;
    var $errs = "errs__" + $lvl;
    var $it = it2.util.copy(it2);
    var $closingBraces = "";
    $it.level++;
    var $nextValid = "valid" + $it.level;
    var $currentBaseId = $it.baseId, $prevValid = "prevValid" + $lvl, $passingSchemas = "passingSchemas" + $lvl;
    out += "var " + $errs + " = errors , " + $prevValid + " = false , " + $valid + " = false , " + $passingSchemas + " = null; ";
    var $wasComposite = it2.compositeRule;
    it2.compositeRule = $it.compositeRule = true;
    var arr1 = $schema2;
    if (arr1) {
      var $sch, $i = -1, l1 = arr1.length - 1;
      while ($i < l1) {
        $sch = arr1[$i += 1];
        if (it2.opts.strictKeywords ? typeof $sch == "object" && Object.keys($sch).length > 0 || $sch === false : it2.util.schemaHasRules($sch, it2.RULES.all)) {
          $it.schema = $sch;
          $it.schemaPath = $schemaPath + "[" + $i + "]";
          $it.errSchemaPath = $errSchemaPath + "/" + $i;
          out += "  " + it2.validate($it) + " ";
          $it.baseId = $currentBaseId;
        } else {
          out += " var " + $nextValid + " = true; ";
        }
        if ($i) {
          out += " if (" + $nextValid + " && " + $prevValid + ") { " + $valid + " = false; " + $passingSchemas + " = [" + $passingSchemas + ", " + $i + "]; } else { ";
          $closingBraces += "}";
        }
        out += " if (" + $nextValid + ") { " + $valid + " = " + $prevValid + " = true; " + $passingSchemas + " = " + $i + "; }";
      }
    }
    it2.compositeRule = $it.compositeRule = $wasComposite;
    out += "" + $closingBraces + "if (!" + $valid + ") {   var err =   ";
    if (it2.createErrors !== false) {
      out += " { keyword: 'oneOf' , dataPath: (dataPath || '') + " + it2.errorPath + " , schemaPath: " + it2.util.toQuotedString($errSchemaPath) + " , params: { passingSchemas: " + $passingSchemas + " } ";
      if (it2.opts.messages !== false) {
        out += " , message: 'should match exactly one schema in oneOf' ";
      }
      if (it2.opts.verbose) {
        out += " , schema: validate.schema" + $schemaPath + " , parentSchema: validate.schema" + it2.schemaPath + " , data: " + $data + " ";
      }
      out += " } ";
    } else {
      out += " {} ";
    }
    out += ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ";
    if (!it2.compositeRule && $breakOnError) {
      if (it2.async) {
        out += " throw new ValidationError(vErrors); ";
      } else {
        out += " validate.errors = vErrors; return false; ";
      }
    }
    out += "} else {  errors = " + $errs + "; if (vErrors !== null) { if (" + $errs + ") vErrors.length = " + $errs + "; else vErrors = null; }";
    if (it2.opts.allErrors) {
      out += " } ";
    }
    return out;
  };
  return oneOf;
}
var pattern;
var hasRequiredPattern;
function requirePattern() {
  if (hasRequiredPattern) return pattern;
  hasRequiredPattern = 1;
  pattern = function generate_pattern(it2, $keyword, $ruleType) {
    var out = " ";
    var $lvl = it2.level;
    var $dataLvl = it2.dataLevel;
    var $schema2 = it2.schema[$keyword];
    var $schemaPath = it2.schemaPath + it2.util.getProperty($keyword);
    var $errSchemaPath = it2.errSchemaPath + "/" + $keyword;
    var $breakOnError = !it2.opts.allErrors;
    var $data = "data" + ($dataLvl || "");
    var $isData = it2.opts.$data && $schema2 && $schema2.$data, $schemaValue;
    if ($isData) {
      out += " var schema" + $lvl + " = " + it2.util.getData($schema2.$data, $dataLvl, it2.dataPathArr) + "; ";
      $schemaValue = "schema" + $lvl;
    } else {
      $schemaValue = $schema2;
    }
    var $regexp = $isData ? "(new RegExp(" + $schemaValue + "))" : it2.usePattern($schema2);
    out += "if ( ";
    if ($isData) {
      out += " (" + $schemaValue + " !== undefined && typeof " + $schemaValue + " != 'string') || ";
    }
    out += " !" + $regexp + ".test(" + $data + ") ) {   ";
    var $$outStack = $$outStack || [];
    $$outStack.push(out);
    out = "";
    if (it2.createErrors !== false) {
      out += " { keyword: 'pattern' , dataPath: (dataPath || '') + " + it2.errorPath + " , schemaPath: " + it2.util.toQuotedString($errSchemaPath) + " , params: { pattern:  ";
      if ($isData) {
        out += "" + $schemaValue;
      } else {
        out += "" + it2.util.toQuotedString($schema2);
      }
      out += "  } ";
      if (it2.opts.messages !== false) {
        out += ` , message: 'should match pattern "`;
        if ($isData) {
          out += "' + " + $schemaValue + " + '";
        } else {
          out += "" + it2.util.escapeQuotes($schema2);
        }
        out += `"' `;
      }
      if (it2.opts.verbose) {
        out += " , schema:  ";
        if ($isData) {
          out += "validate.schema" + $schemaPath;
        } else {
          out += "" + it2.util.toQuotedString($schema2);
        }
        out += "         , parentSchema: validate.schema" + it2.schemaPath + " , data: " + $data + " ";
      }
      out += " } ";
    } else {
      out += " {} ";
    }
    var __err = out;
    out = $$outStack.pop();
    if (!it2.compositeRule && $breakOnError) {
      if (it2.async) {
        out += " throw new ValidationError([" + __err + "]); ";
      } else {
        out += " validate.errors = [" + __err + "]; return false; ";
      }
    } else {
      out += " var err = " + __err + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ";
    }
    out += "} ";
    if ($breakOnError) {
      out += " else { ";
    }
    return out;
  };
  return pattern;
}
var properties$2;
var hasRequiredProperties;
function requireProperties() {
  if (hasRequiredProperties) return properties$2;
  hasRequiredProperties = 1;
  properties$2 = function generate_properties(it2, $keyword, $ruleType) {
    var out = " ";
    var $lvl = it2.level;
    var $dataLvl = it2.dataLevel;
    var $schema2 = it2.schema[$keyword];
    var $schemaPath = it2.schemaPath + it2.util.getProperty($keyword);
    var $errSchemaPath = it2.errSchemaPath + "/" + $keyword;
    var $breakOnError = !it2.opts.allErrors;
    var $data = "data" + ($dataLvl || "");
    var $errs = "errs__" + $lvl;
    var $it = it2.util.copy(it2);
    var $closingBraces = "";
    $it.level++;
    var $nextValid = "valid" + $it.level;
    var $key = "key" + $lvl, $idx = "idx" + $lvl, $dataNxt = $it.dataLevel = it2.dataLevel + 1, $nextData = "data" + $dataNxt, $dataProperties = "dataProperties" + $lvl;
    var $schemaKeys = Object.keys($schema2 || {}).filter(notProto), $pProperties = it2.schema.patternProperties || {}, $pPropertyKeys = Object.keys($pProperties).filter(notProto), $aProperties = it2.schema.additionalProperties, $someProperties = $schemaKeys.length || $pPropertyKeys.length, $noAdditional = $aProperties === false, $additionalIsSchema = typeof $aProperties == "object" && Object.keys($aProperties).length, $removeAdditional = it2.opts.removeAdditional, $checkAdditional = $noAdditional || $additionalIsSchema || $removeAdditional, $ownProperties = it2.opts.ownProperties, $currentBaseId = it2.baseId;
    var $required = it2.schema.required;
    if ($required && !(it2.opts.$data && $required.$data) && $required.length < it2.opts.loopRequired) {
      var $requiredHash = it2.util.toHash($required);
    }
    function notProto(p2) {
      return p2 !== "__proto__";
    }
    out += "var " + $errs + " = errors;var " + $nextValid + " = true;";
    if ($ownProperties) {
      out += " var " + $dataProperties + " = undefined;";
    }
    if ($checkAdditional) {
      if ($ownProperties) {
        out += " " + $dataProperties + " = " + $dataProperties + " || Object.keys(" + $data + "); for (var " + $idx + "=0; " + $idx + "<" + $dataProperties + ".length; " + $idx + "++) { var " + $key + " = " + $dataProperties + "[" + $idx + "]; ";
      } else {
        out += " for (var " + $key + " in " + $data + ") { ";
      }
      if ($someProperties) {
        out += " var isAdditional" + $lvl + " = !(false ";
        if ($schemaKeys.length) {
          if ($schemaKeys.length > 8) {
            out += " || validate.schema" + $schemaPath + ".hasOwnProperty(" + $key + ") ";
          } else {
            var arr1 = $schemaKeys;
            if (arr1) {
              var $propertyKey, i1 = -1, l1 = arr1.length - 1;
              while (i1 < l1) {
                $propertyKey = arr1[i1 += 1];
                out += " || " + $key + " == " + it2.util.toQuotedString($propertyKey) + " ";
              }
            }
          }
        }
        if ($pPropertyKeys.length) {
          var arr2 = $pPropertyKeys;
          if (arr2) {
            var $pProperty, $i = -1, l2 = arr2.length - 1;
            while ($i < l2) {
              $pProperty = arr2[$i += 1];
              out += " || " + it2.usePattern($pProperty) + ".test(" + $key + ") ";
            }
          }
        }
        out += " ); if (isAdditional" + $lvl + ") { ";
      }
      if ($removeAdditional == "all") {
        out += " delete " + $data + "[" + $key + "]; ";
      } else {
        var $currentErrorPath = it2.errorPath;
        var $additionalProperty = "' + " + $key + " + '";
        if (it2.opts._errorDataPathProperty) {
          it2.errorPath = it2.util.getPathExpr(it2.errorPath, $key, it2.opts.jsonPointers);
        }
        if ($noAdditional) {
          if ($removeAdditional) {
            out += " delete " + $data + "[" + $key + "]; ";
          } else {
            out += " " + $nextValid + " = false; ";
            var $currErrSchemaPath = $errSchemaPath;
            $errSchemaPath = it2.errSchemaPath + "/additionalProperties";
            var $$outStack = $$outStack || [];
            $$outStack.push(out);
            out = "";
            if (it2.createErrors !== false) {
              out += " { keyword: 'additionalProperties' , dataPath: (dataPath || '') + " + it2.errorPath + " , schemaPath: " + it2.util.toQuotedString($errSchemaPath) + " , params: { additionalProperty: '" + $additionalProperty + "' } ";
              if (it2.opts.messages !== false) {
                out += " , message: '";
                if (it2.opts._errorDataPathProperty) {
                  out += "is an invalid additional property";
                } else {
                  out += "should NOT have additional properties";
                }
                out += "' ";
              }
              if (it2.opts.verbose) {
                out += " , schema: false , parentSchema: validate.schema" + it2.schemaPath + " , data: " + $data + " ";
              }
              out += " } ";
            } else {
              out += " {} ";
            }
            var __err = out;
            out = $$outStack.pop();
            if (!it2.compositeRule && $breakOnError) {
              if (it2.async) {
                out += " throw new ValidationError([" + __err + "]); ";
              } else {
                out += " validate.errors = [" + __err + "]; return false; ";
              }
            } else {
              out += " var err = " + __err + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ";
            }
            $errSchemaPath = $currErrSchemaPath;
            if ($breakOnError) {
              out += " break; ";
            }
          }
        } else if ($additionalIsSchema) {
          if ($removeAdditional == "failing") {
            out += " var " + $errs + " = errors;  ";
            var $wasComposite = it2.compositeRule;
            it2.compositeRule = $it.compositeRule = true;
            $it.schema = $aProperties;
            $it.schemaPath = it2.schemaPath + ".additionalProperties";
            $it.errSchemaPath = it2.errSchemaPath + "/additionalProperties";
            $it.errorPath = it2.opts._errorDataPathProperty ? it2.errorPath : it2.util.getPathExpr(it2.errorPath, $key, it2.opts.jsonPointers);
            var $passData = $data + "[" + $key + "]";
            $it.dataPathArr[$dataNxt] = $key;
            var $code = it2.validate($it);
            $it.baseId = $currentBaseId;
            if (it2.util.varOccurences($code, $nextData) < 2) {
              out += " " + it2.util.varReplace($code, $nextData, $passData) + " ";
            } else {
              out += " var " + $nextData + " = " + $passData + "; " + $code + " ";
            }
            out += " if (!" + $nextValid + ") { errors = " + $errs + "; if (validate.errors !== null) { if (errors) validate.errors.length = errors; else validate.errors = null; } delete " + $data + "[" + $key + "]; }  ";
            it2.compositeRule = $it.compositeRule = $wasComposite;
          } else {
            $it.schema = $aProperties;
            $it.schemaPath = it2.schemaPath + ".additionalProperties";
            $it.errSchemaPath = it2.errSchemaPath + "/additionalProperties";
            $it.errorPath = it2.opts._errorDataPathProperty ? it2.errorPath : it2.util.getPathExpr(it2.errorPath, $key, it2.opts.jsonPointers);
            var $passData = $data + "[" + $key + "]";
            $it.dataPathArr[$dataNxt] = $key;
            var $code = it2.validate($it);
            $it.baseId = $currentBaseId;
            if (it2.util.varOccurences($code, $nextData) < 2) {
              out += " " + it2.util.varReplace($code, $nextData, $passData) + " ";
            } else {
              out += " var " + $nextData + " = " + $passData + "; " + $code + " ";
            }
            if ($breakOnError) {
              out += " if (!" + $nextValid + ") break; ";
            }
          }
        }
        it2.errorPath = $currentErrorPath;
      }
      if ($someProperties) {
        out += " } ";
      }
      out += " }  ";
      if ($breakOnError) {
        out += " if (" + $nextValid + ") { ";
        $closingBraces += "}";
      }
    }
    var $useDefaults = it2.opts.useDefaults && !it2.compositeRule;
    if ($schemaKeys.length) {
      var arr3 = $schemaKeys;
      if (arr3) {
        var $propertyKey, i3 = -1, l3 = arr3.length - 1;
        while (i3 < l3) {
          $propertyKey = arr3[i3 += 1];
          var $sch = $schema2[$propertyKey];
          if (it2.opts.strictKeywords ? typeof $sch == "object" && Object.keys($sch).length > 0 || $sch === false : it2.util.schemaHasRules($sch, it2.RULES.all)) {
            var $prop = it2.util.getProperty($propertyKey), $passData = $data + $prop, $hasDefault = $useDefaults && $sch.default !== void 0;
            $it.schema = $sch;
            $it.schemaPath = $schemaPath + $prop;
            $it.errSchemaPath = $errSchemaPath + "/" + it2.util.escapeFragment($propertyKey);
            $it.errorPath = it2.util.getPath(it2.errorPath, $propertyKey, it2.opts.jsonPointers);
            $it.dataPathArr[$dataNxt] = it2.util.toQuotedString($propertyKey);
            var $code = it2.validate($it);
            $it.baseId = $currentBaseId;
            if (it2.util.varOccurences($code, $nextData) < 2) {
              $code = it2.util.varReplace($code, $nextData, $passData);
              var $useData = $passData;
            } else {
              var $useData = $nextData;
              out += " var " + $nextData + " = " + $passData + "; ";
            }
            if ($hasDefault) {
              out += " " + $code + " ";
            } else {
              if ($requiredHash && $requiredHash[$propertyKey]) {
                out += " if ( " + $useData + " === undefined ";
                if ($ownProperties) {
                  out += " || ! Object.prototype.hasOwnProperty.call(" + $data + ", '" + it2.util.escapeQuotes($propertyKey) + "') ";
                }
                out += ") { " + $nextValid + " = false; ";
                var $currentErrorPath = it2.errorPath, $currErrSchemaPath = $errSchemaPath, $missingProperty = it2.util.escapeQuotes($propertyKey);
                if (it2.opts._errorDataPathProperty) {
                  it2.errorPath = it2.util.getPath($currentErrorPath, $propertyKey, it2.opts.jsonPointers);
                }
                $errSchemaPath = it2.errSchemaPath + "/required";
                var $$outStack = $$outStack || [];
                $$outStack.push(out);
                out = "";
                if (it2.createErrors !== false) {
                  out += " { keyword: 'required' , dataPath: (dataPath || '') + " + it2.errorPath + " , schemaPath: " + it2.util.toQuotedString($errSchemaPath) + " , params: { missingProperty: '" + $missingProperty + "' } ";
                  if (it2.opts.messages !== false) {
                    out += " , message: '";
                    if (it2.opts._errorDataPathProperty) {
                      out += "is a required property";
                    } else {
                      out += "should have required property \\'" + $missingProperty + "\\'";
                    }
                    out += "' ";
                  }
                  if (it2.opts.verbose) {
                    out += " , schema: validate.schema" + $schemaPath + " , parentSchema: validate.schema" + it2.schemaPath + " , data: " + $data + " ";
                  }
                  out += " } ";
                } else {
                  out += " {} ";
                }
                var __err = out;
                out = $$outStack.pop();
                if (!it2.compositeRule && $breakOnError) {
                  if (it2.async) {
                    out += " throw new ValidationError([" + __err + "]); ";
                  } else {
                    out += " validate.errors = [" + __err + "]; return false; ";
                  }
                } else {
                  out += " var err = " + __err + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ";
                }
                $errSchemaPath = $currErrSchemaPath;
                it2.errorPath = $currentErrorPath;
                out += " } else { ";
              } else {
                if ($breakOnError) {
                  out += " if ( " + $useData + " === undefined ";
                  if ($ownProperties) {
                    out += " || ! Object.prototype.hasOwnProperty.call(" + $data + ", '" + it2.util.escapeQuotes($propertyKey) + "') ";
                  }
                  out += ") { " + $nextValid + " = true; } else { ";
                } else {
                  out += " if (" + $useData + " !== undefined ";
                  if ($ownProperties) {
                    out += " &&   Object.prototype.hasOwnProperty.call(" + $data + ", '" + it2.util.escapeQuotes($propertyKey) + "') ";
                  }
                  out += " ) { ";
                }
              }
              out += " " + $code + " } ";
            }
          }
          if ($breakOnError) {
            out += " if (" + $nextValid + ") { ";
            $closingBraces += "}";
          }
        }
      }
    }
    if ($pPropertyKeys.length) {
      var arr4 = $pPropertyKeys;
      if (arr4) {
        var $pProperty, i4 = -1, l4 = arr4.length - 1;
        while (i4 < l4) {
          $pProperty = arr4[i4 += 1];
          var $sch = $pProperties[$pProperty];
          if (it2.opts.strictKeywords ? typeof $sch == "object" && Object.keys($sch).length > 0 || $sch === false : it2.util.schemaHasRules($sch, it2.RULES.all)) {
            $it.schema = $sch;
            $it.schemaPath = it2.schemaPath + ".patternProperties" + it2.util.getProperty($pProperty);
            $it.errSchemaPath = it2.errSchemaPath + "/patternProperties/" + it2.util.escapeFragment($pProperty);
            if ($ownProperties) {
              out += " " + $dataProperties + " = " + $dataProperties + " || Object.keys(" + $data + "); for (var " + $idx + "=0; " + $idx + "<" + $dataProperties + ".length; " + $idx + "++) { var " + $key + " = " + $dataProperties + "[" + $idx + "]; ";
            } else {
              out += " for (var " + $key + " in " + $data + ") { ";
            }
            out += " if (" + it2.usePattern($pProperty) + ".test(" + $key + ")) { ";
            $it.errorPath = it2.util.getPathExpr(it2.errorPath, $key, it2.opts.jsonPointers);
            var $passData = $data + "[" + $key + "]";
            $it.dataPathArr[$dataNxt] = $key;
            var $code = it2.validate($it);
            $it.baseId = $currentBaseId;
            if (it2.util.varOccurences($code, $nextData) < 2) {
              out += " " + it2.util.varReplace($code, $nextData, $passData) + " ";
            } else {
              out += " var " + $nextData + " = " + $passData + "; " + $code + " ";
            }
            if ($breakOnError) {
              out += " if (!" + $nextValid + ") break; ";
            }
            out += " } ";
            if ($breakOnError) {
              out += " else " + $nextValid + " = true; ";
            }
            out += " }  ";
            if ($breakOnError) {
              out += " if (" + $nextValid + ") { ";
              $closingBraces += "}";
            }
          }
        }
      }
    }
    if ($breakOnError) {
      out += " " + $closingBraces + " if (" + $errs + " == errors) {";
    }
    return out;
  };
  return properties$2;
}
var propertyNames;
var hasRequiredPropertyNames;
function requirePropertyNames() {
  if (hasRequiredPropertyNames) return propertyNames;
  hasRequiredPropertyNames = 1;
  propertyNames = function generate_propertyNames(it2, $keyword, $ruleType) {
    var out = " ";
    var $lvl = it2.level;
    var $dataLvl = it2.dataLevel;
    var $schema2 = it2.schema[$keyword];
    var $schemaPath = it2.schemaPath + it2.util.getProperty($keyword);
    var $errSchemaPath = it2.errSchemaPath + "/" + $keyword;
    var $breakOnError = !it2.opts.allErrors;
    var $data = "data" + ($dataLvl || "");
    var $errs = "errs__" + $lvl;
    var $it = it2.util.copy(it2);
    var $closingBraces = "";
    $it.level++;
    var $nextValid = "valid" + $it.level;
    out += "var " + $errs + " = errors;";
    if (it2.opts.strictKeywords ? typeof $schema2 == "object" && Object.keys($schema2).length > 0 || $schema2 === false : it2.util.schemaHasRules($schema2, it2.RULES.all)) {
      $it.schema = $schema2;
      $it.schemaPath = $schemaPath;
      $it.errSchemaPath = $errSchemaPath;
      var $key = "key" + $lvl, $idx = "idx" + $lvl, $i = "i" + $lvl, $invalidName = "' + " + $key + " + '", $dataNxt = $it.dataLevel = it2.dataLevel + 1, $nextData = "data" + $dataNxt, $dataProperties = "dataProperties" + $lvl, $ownProperties = it2.opts.ownProperties, $currentBaseId = it2.baseId;
      if ($ownProperties) {
        out += " var " + $dataProperties + " = undefined; ";
      }
      if ($ownProperties) {
        out += " " + $dataProperties + " = " + $dataProperties + " || Object.keys(" + $data + "); for (var " + $idx + "=0; " + $idx + "<" + $dataProperties + ".length; " + $idx + "++) { var " + $key + " = " + $dataProperties + "[" + $idx + "]; ";
      } else {
        out += " for (var " + $key + " in " + $data + ") { ";
      }
      out += " var startErrs" + $lvl + " = errors; ";
      var $passData = $key;
      var $wasComposite = it2.compositeRule;
      it2.compositeRule = $it.compositeRule = true;
      var $code = it2.validate($it);
      $it.baseId = $currentBaseId;
      if (it2.util.varOccurences($code, $nextData) < 2) {
        out += " " + it2.util.varReplace($code, $nextData, $passData) + " ";
      } else {
        out += " var " + $nextData + " = " + $passData + "; " + $code + " ";
      }
      it2.compositeRule = $it.compositeRule = $wasComposite;
      out += " if (!" + $nextValid + ") { for (var " + $i + "=startErrs" + $lvl + "; " + $i + "<errors; " + $i + "++) { vErrors[" + $i + "].propertyName = " + $key + "; }   var err =   ";
      if (it2.createErrors !== false) {
        out += " { keyword: 'propertyNames' , dataPath: (dataPath || '') + " + it2.errorPath + " , schemaPath: " + it2.util.toQuotedString($errSchemaPath) + " , params: { propertyName: '" + $invalidName + "' } ";
        if (it2.opts.messages !== false) {
          out += " , message: 'property name \\'" + $invalidName + "\\' is invalid' ";
        }
        if (it2.opts.verbose) {
          out += " , schema: validate.schema" + $schemaPath + " , parentSchema: validate.schema" + it2.schemaPath + " , data: " + $data + " ";
        }
        out += " } ";
      } else {
        out += " {} ";
      }
      out += ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ";
      if (!it2.compositeRule && $breakOnError) {
        if (it2.async) {
          out += " throw new ValidationError(vErrors); ";
        } else {
          out += " validate.errors = vErrors; return false; ";
        }
      }
      if ($breakOnError) {
        out += " break; ";
      }
      out += " } }";
    }
    if ($breakOnError) {
      out += " " + $closingBraces + " if (" + $errs + " == errors) {";
    }
    return out;
  };
  return propertyNames;
}
var required$1;
var hasRequiredRequired;
function requireRequired() {
  if (hasRequiredRequired) return required$1;
  hasRequiredRequired = 1;
  required$1 = function generate_required(it2, $keyword, $ruleType) {
    var out = " ";
    var $lvl = it2.level;
    var $dataLvl = it2.dataLevel;
    var $schema2 = it2.schema[$keyword];
    var $schemaPath = it2.schemaPath + it2.util.getProperty($keyword);
    var $errSchemaPath = it2.errSchemaPath + "/" + $keyword;
    var $breakOnError = !it2.opts.allErrors;
    var $data = "data" + ($dataLvl || "");
    var $valid = "valid" + $lvl;
    var $isData = it2.opts.$data && $schema2 && $schema2.$data;
    if ($isData) {
      out += " var schema" + $lvl + " = " + it2.util.getData($schema2.$data, $dataLvl, it2.dataPathArr) + "; ";
    }
    var $vSchema = "schema" + $lvl;
    if (!$isData) {
      if ($schema2.length < it2.opts.loopRequired && it2.schema.properties && Object.keys(it2.schema.properties).length) {
        var $required = [];
        var arr1 = $schema2;
        if (arr1) {
          var $property, i1 = -1, l1 = arr1.length - 1;
          while (i1 < l1) {
            $property = arr1[i1 += 1];
            var $propertySch = it2.schema.properties[$property];
            if (!($propertySch && (it2.opts.strictKeywords ? typeof $propertySch == "object" && Object.keys($propertySch).length > 0 || $propertySch === false : it2.util.schemaHasRules($propertySch, it2.RULES.all)))) {
              $required[$required.length] = $property;
            }
          }
        }
      } else {
        var $required = $schema2;
      }
    }
    if ($isData || $required.length) {
      var $currentErrorPath = it2.errorPath, $loopRequired = $isData || $required.length >= it2.opts.loopRequired, $ownProperties = it2.opts.ownProperties;
      if ($breakOnError) {
        out += " var missing" + $lvl + "; ";
        if ($loopRequired) {
          if (!$isData) {
            out += " var " + $vSchema + " = validate.schema" + $schemaPath + "; ";
          }
          var $i = "i" + $lvl, $propertyPath = "schema" + $lvl + "[" + $i + "]", $missingProperty = "' + " + $propertyPath + " + '";
          if (it2.opts._errorDataPathProperty) {
            it2.errorPath = it2.util.getPathExpr($currentErrorPath, $propertyPath, it2.opts.jsonPointers);
          }
          out += " var " + $valid + " = true; ";
          if ($isData) {
            out += " if (schema" + $lvl + " === undefined) " + $valid + " = true; else if (!Array.isArray(schema" + $lvl + ")) " + $valid + " = false; else {";
          }
          out += " for (var " + $i + " = 0; " + $i + " < " + $vSchema + ".length; " + $i + "++) { " + $valid + " = " + $data + "[" + $vSchema + "[" + $i + "]] !== undefined ";
          if ($ownProperties) {
            out += " &&   Object.prototype.hasOwnProperty.call(" + $data + ", " + $vSchema + "[" + $i + "]) ";
          }
          out += "; if (!" + $valid + ") break; } ";
          if ($isData) {
            out += "  }  ";
          }
          out += "  if (!" + $valid + ") {   ";
          var $$outStack = $$outStack || [];
          $$outStack.push(out);
          out = "";
          if (it2.createErrors !== false) {
            out += " { keyword: 'required' , dataPath: (dataPath || '') + " + it2.errorPath + " , schemaPath: " + it2.util.toQuotedString($errSchemaPath) + " , params: { missingProperty: '" + $missingProperty + "' } ";
            if (it2.opts.messages !== false) {
              out += " , message: '";
              if (it2.opts._errorDataPathProperty) {
                out += "is a required property";
              } else {
                out += "should have required property \\'" + $missingProperty + "\\'";
              }
              out += "' ";
            }
            if (it2.opts.verbose) {
              out += " , schema: validate.schema" + $schemaPath + " , parentSchema: validate.schema" + it2.schemaPath + " , data: " + $data + " ";
            }
            out += " } ";
          } else {
            out += " {} ";
          }
          var __err = out;
          out = $$outStack.pop();
          if (!it2.compositeRule && $breakOnError) {
            if (it2.async) {
              out += " throw new ValidationError([" + __err + "]); ";
            } else {
              out += " validate.errors = [" + __err + "]; return false; ";
            }
          } else {
            out += " var err = " + __err + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ";
          }
          out += " } else { ";
        } else {
          out += " if ( ";
          var arr2 = $required;
          if (arr2) {
            var $propertyKey, $i = -1, l2 = arr2.length - 1;
            while ($i < l2) {
              $propertyKey = arr2[$i += 1];
              if ($i) {
                out += " || ";
              }
              var $prop = it2.util.getProperty($propertyKey), $useData = $data + $prop;
              out += " ( ( " + $useData + " === undefined ";
              if ($ownProperties) {
                out += " || ! Object.prototype.hasOwnProperty.call(" + $data + ", '" + it2.util.escapeQuotes($propertyKey) + "') ";
              }
              out += ") && (missing" + $lvl + " = " + it2.util.toQuotedString(it2.opts.jsonPointers ? $propertyKey : $prop) + ") ) ";
            }
          }
          out += ") {  ";
          var $propertyPath = "missing" + $lvl, $missingProperty = "' + " + $propertyPath + " + '";
          if (it2.opts._errorDataPathProperty) {
            it2.errorPath = it2.opts.jsonPointers ? it2.util.getPathExpr($currentErrorPath, $propertyPath, true) : $currentErrorPath + " + " + $propertyPath;
          }
          var $$outStack = $$outStack || [];
          $$outStack.push(out);
          out = "";
          if (it2.createErrors !== false) {
            out += " { keyword: 'required' , dataPath: (dataPath || '') + " + it2.errorPath + " , schemaPath: " + it2.util.toQuotedString($errSchemaPath) + " , params: { missingProperty: '" + $missingProperty + "' } ";
            if (it2.opts.messages !== false) {
              out += " , message: '";
              if (it2.opts._errorDataPathProperty) {
                out += "is a required property";
              } else {
                out += "should have required property \\'" + $missingProperty + "\\'";
              }
              out += "' ";
            }
            if (it2.opts.verbose) {
              out += " , schema: validate.schema" + $schemaPath + " , parentSchema: validate.schema" + it2.schemaPath + " , data: " + $data + " ";
            }
            out += " } ";
          } else {
            out += " {} ";
          }
          var __err = out;
          out = $$outStack.pop();
          if (!it2.compositeRule && $breakOnError) {
            if (it2.async) {
              out += " throw new ValidationError([" + __err + "]); ";
            } else {
              out += " validate.errors = [" + __err + "]; return false; ";
            }
          } else {
            out += " var err = " + __err + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ";
          }
          out += " } else { ";
        }
      } else {
        if ($loopRequired) {
          if (!$isData) {
            out += " var " + $vSchema + " = validate.schema" + $schemaPath + "; ";
          }
          var $i = "i" + $lvl, $propertyPath = "schema" + $lvl + "[" + $i + "]", $missingProperty = "' + " + $propertyPath + " + '";
          if (it2.opts._errorDataPathProperty) {
            it2.errorPath = it2.util.getPathExpr($currentErrorPath, $propertyPath, it2.opts.jsonPointers);
          }
          if ($isData) {
            out += " if (" + $vSchema + " && !Array.isArray(" + $vSchema + ")) {  var err =   ";
            if (it2.createErrors !== false) {
              out += " { keyword: 'required' , dataPath: (dataPath || '') + " + it2.errorPath + " , schemaPath: " + it2.util.toQuotedString($errSchemaPath) + " , params: { missingProperty: '" + $missingProperty + "' } ";
              if (it2.opts.messages !== false) {
                out += " , message: '";
                if (it2.opts._errorDataPathProperty) {
                  out += "is a required property";
                } else {
                  out += "should have required property \\'" + $missingProperty + "\\'";
                }
                out += "' ";
              }
              if (it2.opts.verbose) {
                out += " , schema: validate.schema" + $schemaPath + " , parentSchema: validate.schema" + it2.schemaPath + " , data: " + $data + " ";
              }
              out += " } ";
            } else {
              out += " {} ";
            }
            out += ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; } else if (" + $vSchema + " !== undefined) { ";
          }
          out += " for (var " + $i + " = 0; " + $i + " < " + $vSchema + ".length; " + $i + "++) { if (" + $data + "[" + $vSchema + "[" + $i + "]] === undefined ";
          if ($ownProperties) {
            out += " || ! Object.prototype.hasOwnProperty.call(" + $data + ", " + $vSchema + "[" + $i + "]) ";
          }
          out += ") {  var err =   ";
          if (it2.createErrors !== false) {
            out += " { keyword: 'required' , dataPath: (dataPath || '') + " + it2.errorPath + " , schemaPath: " + it2.util.toQuotedString($errSchemaPath) + " , params: { missingProperty: '" + $missingProperty + "' } ";
            if (it2.opts.messages !== false) {
              out += " , message: '";
              if (it2.opts._errorDataPathProperty) {
                out += "is a required property";
              } else {
                out += "should have required property \\'" + $missingProperty + "\\'";
              }
              out += "' ";
            }
            if (it2.opts.verbose) {
              out += " , schema: validate.schema" + $schemaPath + " , parentSchema: validate.schema" + it2.schemaPath + " , data: " + $data + " ";
            }
            out += " } ";
          } else {
            out += " {} ";
          }
          out += ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; } } ";
          if ($isData) {
            out += "  }  ";
          }
        } else {
          var arr3 = $required;
          if (arr3) {
            var $propertyKey, i3 = -1, l3 = arr3.length - 1;
            while (i3 < l3) {
              $propertyKey = arr3[i3 += 1];
              var $prop = it2.util.getProperty($propertyKey), $missingProperty = it2.util.escapeQuotes($propertyKey), $useData = $data + $prop;
              if (it2.opts._errorDataPathProperty) {
                it2.errorPath = it2.util.getPath($currentErrorPath, $propertyKey, it2.opts.jsonPointers);
              }
              out += " if ( " + $useData + " === undefined ";
              if ($ownProperties) {
                out += " || ! Object.prototype.hasOwnProperty.call(" + $data + ", '" + it2.util.escapeQuotes($propertyKey) + "') ";
              }
              out += ") {  var err =   ";
              if (it2.createErrors !== false) {
                out += " { keyword: 'required' , dataPath: (dataPath || '') + " + it2.errorPath + " , schemaPath: " + it2.util.toQuotedString($errSchemaPath) + " , params: { missingProperty: '" + $missingProperty + "' } ";
                if (it2.opts.messages !== false) {
                  out += " , message: '";
                  if (it2.opts._errorDataPathProperty) {
                    out += "is a required property";
                  } else {
                    out += "should have required property \\'" + $missingProperty + "\\'";
                  }
                  out += "' ";
                }
                if (it2.opts.verbose) {
                  out += " , schema: validate.schema" + $schemaPath + " , parentSchema: validate.schema" + it2.schemaPath + " , data: " + $data + " ";
                }
                out += " } ";
              } else {
                out += " {} ";
              }
              out += ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; } ";
            }
          }
        }
      }
      it2.errorPath = $currentErrorPath;
    } else if ($breakOnError) {
      out += " if (true) {";
    }
    return out;
  };
  return required$1;
}
var uniqueItems;
var hasRequiredUniqueItems;
function requireUniqueItems() {
  if (hasRequiredUniqueItems) return uniqueItems;
  hasRequiredUniqueItems = 1;
  uniqueItems = function generate_uniqueItems(it2, $keyword, $ruleType) {
    var out = " ";
    var $lvl = it2.level;
    var $dataLvl = it2.dataLevel;
    var $schema2 = it2.schema[$keyword];
    var $schemaPath = it2.schemaPath + it2.util.getProperty($keyword);
    var $errSchemaPath = it2.errSchemaPath + "/" + $keyword;
    var $breakOnError = !it2.opts.allErrors;
    var $data = "data" + ($dataLvl || "");
    var $valid = "valid" + $lvl;
    var $isData = it2.opts.$data && $schema2 && $schema2.$data, $schemaValue;
    if ($isData) {
      out += " var schema" + $lvl + " = " + it2.util.getData($schema2.$data, $dataLvl, it2.dataPathArr) + "; ";
      $schemaValue = "schema" + $lvl;
    } else {
      $schemaValue = $schema2;
    }
    if (($schema2 || $isData) && it2.opts.uniqueItems !== false) {
      if ($isData) {
        out += " var " + $valid + "; if (" + $schemaValue + " === false || " + $schemaValue + " === undefined) " + $valid + " = true; else if (typeof " + $schemaValue + " != 'boolean') " + $valid + " = false; else { ";
      }
      out += " var i = " + $data + ".length , " + $valid + " = true , j; if (i > 1) { ";
      var $itemType = it2.schema.items && it2.schema.items.type, $typeIsArray = Array.isArray($itemType);
      if (!$itemType || $itemType == "object" || $itemType == "array" || $typeIsArray && ($itemType.indexOf("object") >= 0 || $itemType.indexOf("array") >= 0)) {
        out += " outer: for (;i--;) { for (j = i; j--;) { if (equal(" + $data + "[i], " + $data + "[j])) { " + $valid + " = false; break outer; } } } ";
      } else {
        out += " var itemIndices = {}, item; for (;i--;) { var item = " + $data + "[i]; ";
        var $method = "checkDataType" + ($typeIsArray ? "s" : "");
        out += " if (" + it2.util[$method]($itemType, "item", it2.opts.strictNumbers, true) + ") continue; ";
        if ($typeIsArray) {
          out += ` if (typeof item == 'string') item = '"' + item; `;
        }
        out += " if (typeof itemIndices[item] == 'number') { " + $valid + " = false; j = itemIndices[item]; break; } itemIndices[item] = i; } ";
      }
      out += " } ";
      if ($isData) {
        out += "  }  ";
      }
      out += " if (!" + $valid + ") {   ";
      var $$outStack = $$outStack || [];
      $$outStack.push(out);
      out = "";
      if (it2.createErrors !== false) {
        out += " { keyword: 'uniqueItems' , dataPath: (dataPath || '') + " + it2.errorPath + " , schemaPath: " + it2.util.toQuotedString($errSchemaPath) + " , params: { i: i, j: j } ";
        if (it2.opts.messages !== false) {
          out += " , message: 'should NOT have duplicate items (items ## ' + j + ' and ' + i + ' are identical)' ";
        }
        if (it2.opts.verbose) {
          out += " , schema:  ";
          if ($isData) {
            out += "validate.schema" + $schemaPath;
          } else {
            out += "" + $schema2;
          }
          out += "         , parentSchema: validate.schema" + it2.schemaPath + " , data: " + $data + " ";
        }
        out += " } ";
      } else {
        out += " {} ";
      }
      var __err = out;
      out = $$outStack.pop();
      if (!it2.compositeRule && $breakOnError) {
        if (it2.async) {
          out += " throw new ValidationError([" + __err + "]); ";
        } else {
          out += " validate.errors = [" + __err + "]; return false; ";
        }
      } else {
        out += " var err = " + __err + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ";
      }
      out += " } ";
      if ($breakOnError) {
        out += " else { ";
      }
    } else {
      if ($breakOnError) {
        out += " if (true) { ";
      }
    }
    return out;
  };
  return uniqueItems;
}
var dotjs;
var hasRequiredDotjs;
function requireDotjs() {
  if (hasRequiredDotjs) return dotjs;
  hasRequiredDotjs = 1;
  dotjs = {
    "$ref": requireRef(),
    allOf: requireAllOf(),
    anyOf: requireAnyOf(),
    "$comment": requireComment(),
    const: require_const(),
    contains: requireContains(),
    dependencies: requireDependencies(),
    "enum": require_enum(),
    format: requireFormat(),
    "if": require_if(),
    items: requireItems(),
    maximum: require_limit(),
    minimum: require_limit(),
    maxItems: require_limitItems(),
    minItems: require_limitItems(),
    maxLength: require_limitLength(),
    minLength: require_limitLength(),
    maxProperties: require_limitProperties(),
    minProperties: require_limitProperties(),
    multipleOf: requireMultipleOf(),
    not: requireNot(),
    oneOf: requireOneOf(),
    pattern: requirePattern(),
    properties: requireProperties(),
    propertyNames: requirePropertyNames(),
    required: requireRequired(),
    uniqueItems: requireUniqueItems(),
    validate: requireValidate()
  };
  return dotjs;
}
var rules;
var hasRequiredRules;
function requireRules() {
  if (hasRequiredRules) return rules;
  hasRequiredRules = 1;
  var ruleModules = requireDotjs(), toHash = requireUtil().toHash;
  rules = function rules2() {
    var RULES = [
      {
        type: "number",
        rules: [
          { "maximum": ["exclusiveMaximum"] },
          { "minimum": ["exclusiveMinimum"] },
          "multipleOf",
          "format"
        ]
      },
      {
        type: "string",
        rules: ["maxLength", "minLength", "pattern", "format"]
      },
      {
        type: "array",
        rules: ["maxItems", "minItems", "items", "contains", "uniqueItems"]
      },
      {
        type: "object",
        rules: [
          "maxProperties",
          "minProperties",
          "required",
          "dependencies",
          "propertyNames",
          { "properties": ["additionalProperties", "patternProperties"] }
        ]
      },
      { rules: ["$ref", "const", "enum", "not", "anyOf", "oneOf", "allOf", "if"] }
    ];
    var ALL = ["type", "$comment"];
    var KEYWORDS = [
      "$schema",
      "$id",
      "id",
      "$data",
      "$async",
      "title",
      "description",
      "default",
      "definitions",
      "examples",
      "readOnly",
      "writeOnly",
      "contentMediaType",
      "contentEncoding",
      "additionalItems",
      "then",
      "else"
    ];
    var TYPES = ["number", "integer", "string", "array", "object", "boolean", "null"];
    RULES.all = toHash(ALL);
    RULES.types = toHash(TYPES);
    RULES.forEach(function(group) {
      group.rules = group.rules.map(function(keyword2) {
        var implKeywords;
        if (typeof keyword2 == "object") {
          var key = Object.keys(keyword2)[0];
          implKeywords = keyword2[key];
          keyword2 = key;
          implKeywords.forEach(function(k2) {
            ALL.push(k2);
            RULES.all[k2] = true;
          });
        }
        ALL.push(keyword2);
        var rule = RULES.all[keyword2] = {
          keyword: keyword2,
          code: ruleModules[keyword2],
          implements: implKeywords
        };
        return rule;
      });
      RULES.all.$comment = {
        keyword: "$comment",
        code: ruleModules.$comment
      };
      if (group.type) RULES.types[group.type] = group;
    });
    RULES.keywords = toHash(ALL.concat(KEYWORDS));
    RULES.custom = {};
    return RULES;
  };
  return rules;
}
var data;
var hasRequiredData;
function requireData() {
  if (hasRequiredData) return data;
  hasRequiredData = 1;
  var KEYWORDS = [
    "multipleOf",
    "maximum",
    "exclusiveMaximum",
    "minimum",
    "exclusiveMinimum",
    "maxLength",
    "minLength",
    "pattern",
    "additionalItems",
    "maxItems",
    "minItems",
    "uniqueItems",
    "maxProperties",
    "minProperties",
    "required",
    "additionalProperties",
    "enum",
    "format",
    "const"
  ];
  data = function(metaSchema, keywordsJsonPointers) {
    for (var i2 = 0; i2 < keywordsJsonPointers.length; i2++) {
      metaSchema = JSON.parse(JSON.stringify(metaSchema));
      var segments = keywordsJsonPointers[i2].split("/");
      var keywords = metaSchema;
      var j2;
      for (j2 = 1; j2 < segments.length; j2++)
        keywords = keywords[segments[j2]];
      for (j2 = 0; j2 < KEYWORDS.length; j2++) {
        var key = KEYWORDS[j2];
        var schema = keywords[key];
        if (schema) {
          keywords[key] = {
            anyOf: [
              schema,
              { $ref: "https://raw.githubusercontent.com/ajv-validator/ajv/master/lib/refs/data.json#" }
            ]
          };
        }
      }
    }
    return metaSchema;
  };
  return data;
}
var async;
var hasRequiredAsync;
function requireAsync() {
  if (hasRequiredAsync) return async;
  hasRequiredAsync = 1;
  var MissingRefError = requireError_classes().MissingRef;
  async = compileAsync;
  function compileAsync(schema, meta, callback) {
    var self = this;
    if (typeof this._opts.loadSchema != "function")
      throw new Error("options.loadSchema should be a function");
    if (typeof meta == "function") {
      callback = meta;
      meta = void 0;
    }
    var p2 = loadMetaSchemaOf(schema).then(function() {
      var schemaObj = self._addSchema(schema, void 0, meta);
      return schemaObj.validate || _compileAsync(schemaObj);
    });
    if (callback) {
      p2.then(
        function(v2) {
          callback(null, v2);
        },
        callback
      );
    }
    return p2;
    function loadMetaSchemaOf(sch) {
      var $schema2 = sch.$schema;
      return $schema2 && !self.getSchema($schema2) ? compileAsync.call(self, { $ref: $schema2 }, true) : Promise.resolve();
    }
    function _compileAsync(schemaObj) {
      try {
        return self._compile(schemaObj);
      } catch (e2) {
        if (e2 instanceof MissingRefError) return loadMissingSchema(e2);
        throw e2;
      }
      function loadMissingSchema(e2) {
        var ref2 = e2.missingSchema;
        if (added(ref2)) throw new Error("Schema " + ref2 + " is loaded but " + e2.missingRef + " cannot be resolved");
        var schemaPromise = self._loadingSchemas[ref2];
        if (!schemaPromise) {
          schemaPromise = self._loadingSchemas[ref2] = self._opts.loadSchema(ref2);
          schemaPromise.then(removePromise, removePromise);
        }
        return schemaPromise.then(function(sch) {
          if (!added(ref2)) {
            return loadMetaSchemaOf(sch).then(function() {
              if (!added(ref2)) self.addSchema(sch, ref2, void 0, meta);
            });
          }
        }).then(function() {
          return _compileAsync(schemaObj);
        });
        function removePromise() {
          delete self._loadingSchemas[ref2];
        }
        function added(ref3) {
          return self._refs[ref3] || self._schemas[ref3];
        }
      }
    }
  }
  return async;
}
var custom;
var hasRequiredCustom;
function requireCustom() {
  if (hasRequiredCustom) return custom;
  hasRequiredCustom = 1;
  custom = function generate_custom(it2, $keyword, $ruleType) {
    var out = " ";
    var $lvl = it2.level;
    var $dataLvl = it2.dataLevel;
    var $schema2 = it2.schema[$keyword];
    var $schemaPath = it2.schemaPath + it2.util.getProperty($keyword);
    var $errSchemaPath = it2.errSchemaPath + "/" + $keyword;
    var $breakOnError = !it2.opts.allErrors;
    var $errorKeyword;
    var $data = "data" + ($dataLvl || "");
    var $valid = "valid" + $lvl;
    var $errs = "errs__" + $lvl;
    var $isData = it2.opts.$data && $schema2 && $schema2.$data, $schemaValue;
    if ($isData) {
      out += " var schema" + $lvl + " = " + it2.util.getData($schema2.$data, $dataLvl, it2.dataPathArr) + "; ";
      $schemaValue = "schema" + $lvl;
    } else {
      $schemaValue = $schema2;
    }
    var $rule = this, $definition = "definition" + $lvl, $rDef = $rule.definition, $closingBraces = "";
    var $compile, $inline, $macro, $ruleValidate, $validateCode;
    if ($isData && $rDef.$data) {
      $validateCode = "keywordValidate" + $lvl;
      var $validateSchema = $rDef.validateSchema;
      out += " var " + $definition + " = RULES.custom['" + $keyword + "'].definition; var " + $validateCode + " = " + $definition + ".validate;";
    } else {
      $ruleValidate = it2.useCustomRule($rule, $schema2, it2.schema, it2);
      if (!$ruleValidate) return;
      $schemaValue = "validate.schema" + $schemaPath;
      $validateCode = $ruleValidate.code;
      $compile = $rDef.compile;
      $inline = $rDef.inline;
      $macro = $rDef.macro;
    }
    var $ruleErrs = $validateCode + ".errors", $i = "i" + $lvl, $ruleErr = "ruleErr" + $lvl, $asyncKeyword = $rDef.async;
    if ($asyncKeyword && !it2.async) throw new Error("async keyword in sync schema");
    if (!($inline || $macro)) {
      out += "" + $ruleErrs + " = null;";
    }
    out += "var " + $errs + " = errors;var " + $valid + ";";
    if ($isData && $rDef.$data) {
      $closingBraces += "}";
      out += " if (" + $schemaValue + " === undefined) { " + $valid + " = true; } else { ";
      if ($validateSchema) {
        $closingBraces += "}";
        out += " " + $valid + " = " + $definition + ".validateSchema(" + $schemaValue + "); if (" + $valid + ") { ";
      }
    }
    if ($inline) {
      if ($rDef.statements) {
        out += " " + $ruleValidate.validate + " ";
      } else {
        out += " " + $valid + " = " + $ruleValidate.validate + "; ";
      }
    } else if ($macro) {
      var $it = it2.util.copy(it2);
      var $closingBraces = "";
      $it.level++;
      var $nextValid = "valid" + $it.level;
      $it.schema = $ruleValidate.validate;
      $it.schemaPath = "";
      var $wasComposite = it2.compositeRule;
      it2.compositeRule = $it.compositeRule = true;
      var $code = it2.validate($it).replace(/validate\.schema/g, $validateCode);
      it2.compositeRule = $it.compositeRule = $wasComposite;
      out += " " + $code;
    } else {
      var $$outStack = $$outStack || [];
      $$outStack.push(out);
      out = "";
      out += "  " + $validateCode + ".call( ";
      if (it2.opts.passContext) {
        out += "this";
      } else {
        out += "self";
      }
      if ($compile || $rDef.schema === false) {
        out += " , " + $data + " ";
      } else {
        out += " , " + $schemaValue + " , " + $data + " , validate.schema" + it2.schemaPath + " ";
      }
      out += " , (dataPath || '')";
      if (it2.errorPath != '""') {
        out += " + " + it2.errorPath;
      }
      var $parentData = $dataLvl ? "data" + ($dataLvl - 1 || "") : "parentData", $parentDataProperty = $dataLvl ? it2.dataPathArr[$dataLvl] : "parentDataProperty";
      out += " , " + $parentData + " , " + $parentDataProperty + " , rootData )  ";
      var def_callRuleValidate = out;
      out = $$outStack.pop();
      if ($rDef.errors === false) {
        out += " " + $valid + " = ";
        if ($asyncKeyword) {
          out += "await ";
        }
        out += "" + def_callRuleValidate + "; ";
      } else {
        if ($asyncKeyword) {
          $ruleErrs = "customErrors" + $lvl;
          out += " var " + $ruleErrs + " = null; try { " + $valid + " = await " + def_callRuleValidate + "; } catch (e) { " + $valid + " = false; if (e instanceof ValidationError) " + $ruleErrs + " = e.errors; else throw e; } ";
        } else {
          out += " " + $ruleErrs + " = null; " + $valid + " = " + def_callRuleValidate + "; ";
        }
      }
    }
    if ($rDef.modifying) {
      out += " if (" + $parentData + ") " + $data + " = " + $parentData + "[" + $parentDataProperty + "];";
    }
    out += "" + $closingBraces;
    if ($rDef.valid) {
      if ($breakOnError) {
        out += " if (true) { ";
      }
    } else {
      out += " if ( ";
      if ($rDef.valid === void 0) {
        out += " !";
        if ($macro) {
          out += "" + $nextValid;
        } else {
          out += "" + $valid;
        }
      } else {
        out += " " + !$rDef.valid + " ";
      }
      out += ") { ";
      $errorKeyword = $rule.keyword;
      var $$outStack = $$outStack || [];
      $$outStack.push(out);
      out = "";
      var $$outStack = $$outStack || [];
      $$outStack.push(out);
      out = "";
      if (it2.createErrors !== false) {
        out += " { keyword: '" + ($errorKeyword || "custom") + "' , dataPath: (dataPath || '') + " + it2.errorPath + " , schemaPath: " + it2.util.toQuotedString($errSchemaPath) + " , params: { keyword: '" + $rule.keyword + "' } ";
        if (it2.opts.messages !== false) {
          out += ` , message: 'should pass "` + $rule.keyword + `" keyword validation' `;
        }
        if (it2.opts.verbose) {
          out += " , schema: validate.schema" + $schemaPath + " , parentSchema: validate.schema" + it2.schemaPath + " , data: " + $data + " ";
        }
        out += " } ";
      } else {
        out += " {} ";
      }
      var __err = out;
      out = $$outStack.pop();
      if (!it2.compositeRule && $breakOnError) {
        if (it2.async) {
          out += " throw new ValidationError([" + __err + "]); ";
        } else {
          out += " validate.errors = [" + __err + "]; return false; ";
        }
      } else {
        out += " var err = " + __err + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ";
      }
      var def_customError = out;
      out = $$outStack.pop();
      if ($inline) {
        if ($rDef.errors) {
          if ($rDef.errors != "full") {
            out += "  for (var " + $i + "=" + $errs + "; " + $i + "<errors; " + $i + "++) { var " + $ruleErr + " = vErrors[" + $i + "]; if (" + $ruleErr + ".dataPath === undefined) " + $ruleErr + ".dataPath = (dataPath || '') + " + it2.errorPath + "; if (" + $ruleErr + ".schemaPath === undefined) { " + $ruleErr + '.schemaPath = "' + $errSchemaPath + '"; } ';
            if (it2.opts.verbose) {
              out += " " + $ruleErr + ".schema = " + $schemaValue + "; " + $ruleErr + ".data = " + $data + "; ";
            }
            out += " } ";
          }
        } else {
          if ($rDef.errors === false) {
            out += " " + def_customError + " ";
          } else {
            out += " if (" + $errs + " == errors) { " + def_customError + " } else {  for (var " + $i + "=" + $errs + "; " + $i + "<errors; " + $i + "++) { var " + $ruleErr + " = vErrors[" + $i + "]; if (" + $ruleErr + ".dataPath === undefined) " + $ruleErr + ".dataPath = (dataPath || '') + " + it2.errorPath + "; if (" + $ruleErr + ".schemaPath === undefined) { " + $ruleErr + '.schemaPath = "' + $errSchemaPath + '"; } ';
            if (it2.opts.verbose) {
              out += " " + $ruleErr + ".schema = " + $schemaValue + "; " + $ruleErr + ".data = " + $data + "; ";
            }
            out += " } } ";
          }
        }
      } else if ($macro) {
        out += "   var err =   ";
        if (it2.createErrors !== false) {
          out += " { keyword: '" + ($errorKeyword || "custom") + "' , dataPath: (dataPath || '') + " + it2.errorPath + " , schemaPath: " + it2.util.toQuotedString($errSchemaPath) + " , params: { keyword: '" + $rule.keyword + "' } ";
          if (it2.opts.messages !== false) {
            out += ` , message: 'should pass "` + $rule.keyword + `" keyword validation' `;
          }
          if (it2.opts.verbose) {
            out += " , schema: validate.schema" + $schemaPath + " , parentSchema: validate.schema" + it2.schemaPath + " , data: " + $data + " ";
          }
          out += " } ";
        } else {
          out += " {} ";
        }
        out += ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ";
        if (!it2.compositeRule && $breakOnError) {
          if (it2.async) {
            out += " throw new ValidationError(vErrors); ";
          } else {
            out += " validate.errors = vErrors; return false; ";
          }
        }
      } else {
        if ($rDef.errors === false) {
          out += " " + def_customError + " ";
        } else {
          out += " if (Array.isArray(" + $ruleErrs + ")) { if (vErrors === null) vErrors = " + $ruleErrs + "; else vErrors = vErrors.concat(" + $ruleErrs + "); errors = vErrors.length;  for (var " + $i + "=" + $errs + "; " + $i + "<errors; " + $i + "++) { var " + $ruleErr + " = vErrors[" + $i + "]; if (" + $ruleErr + ".dataPath === undefined) " + $ruleErr + ".dataPath = (dataPath || '') + " + it2.errorPath + ";  " + $ruleErr + '.schemaPath = "' + $errSchemaPath + '";  ';
          if (it2.opts.verbose) {
            out += " " + $ruleErr + ".schema = " + $schemaValue + "; " + $ruleErr + ".data = " + $data + "; ";
          }
          out += " } } else { " + def_customError + " } ";
        }
      }
      out += " } ";
      if ($breakOnError) {
        out += " else { ";
      }
    }
    return out;
  };
  return custom;
}
const $schema$1 = "http://json-schema.org/draft-07/schema#";
const $id$1 = "http://json-schema.org/draft-07/schema#";
const title = "Core schema meta-schema";
const definitions = { "schemaArray": { "type": "array", "minItems": 1, "items": { "$ref": "#" } }, "nonNegativeInteger": { "type": "integer", "minimum": 0 }, "nonNegativeIntegerDefault0": { "allOf": [{ "$ref": "#/definitions/nonNegativeInteger" }, { "default": 0 }] }, "simpleTypes": { "enum": ["array", "boolean", "integer", "null", "number", "object", "string"] }, "stringArray": { "type": "array", "items": { "type": "string" }, "uniqueItems": true, "default": [] } };
const type$1 = ["object", "boolean"];
const properties$1 = { "$id": { "type": "string", "format": "uri-reference" }, "$schema": { "type": "string", "format": "uri" }, "$ref": { "type": "string", "format": "uri-reference" }, "$comment": { "type": "string" }, "title": { "type": "string" }, "description": { "type": "string" }, "default": true, "readOnly": { "type": "boolean", "default": false }, "examples": { "type": "array", "items": true }, "multipleOf": { "type": "number", "exclusiveMinimum": 0 }, "maximum": { "type": "number" }, "exclusiveMaximum": { "type": "number" }, "minimum": { "type": "number" }, "exclusiveMinimum": { "type": "number" }, "maxLength": { "$ref": "#/definitions/nonNegativeInteger" }, "minLength": { "$ref": "#/definitions/nonNegativeIntegerDefault0" }, "pattern": { "type": "string", "format": "regex" }, "additionalItems": { "$ref": "#" }, "items": { "anyOf": [{ "$ref": "#" }, { "$ref": "#/definitions/schemaArray" }], "default": true }, "maxItems": { "$ref": "#/definitions/nonNegativeInteger" }, "minItems": { "$ref": "#/definitions/nonNegativeIntegerDefault0" }, "uniqueItems": { "type": "boolean", "default": false }, "contains": { "$ref": "#" }, "maxProperties": { "$ref": "#/definitions/nonNegativeInteger" }, "minProperties": { "$ref": "#/definitions/nonNegativeIntegerDefault0" }, "required": { "$ref": "#/definitions/stringArray" }, "additionalProperties": { "$ref": "#" }, "definitions": { "type": "object", "additionalProperties": { "$ref": "#" }, "default": {} }, "properties": { "type": "object", "additionalProperties": { "$ref": "#" }, "default": {} }, "patternProperties": { "type": "object", "additionalProperties": { "$ref": "#" }, "propertyNames": { "format": "regex" }, "default": {} }, "dependencies": { "type": "object", "additionalProperties": { "anyOf": [{ "$ref": "#" }, { "$ref": "#/definitions/stringArray" }] } }, "propertyNames": { "$ref": "#" }, "const": true, "enum": { "type": "array", "items": true, "minItems": 1, "uniqueItems": true }, "type": { "anyOf": [{ "$ref": "#/definitions/simpleTypes" }, { "type": "array", "items": { "$ref": "#/definitions/simpleTypes" }, "minItems": 1, "uniqueItems": true }] }, "format": { "type": "string" }, "contentMediaType": { "type": "string" }, "contentEncoding": { "type": "string" }, "if": { "$ref": "#" }, "then": { "$ref": "#" }, "else": { "$ref": "#" }, "allOf": { "$ref": "#/definitions/schemaArray" }, "anyOf": { "$ref": "#/definitions/schemaArray" }, "oneOf": { "$ref": "#/definitions/schemaArray" }, "not": { "$ref": "#" } };
const require$$13 = {
  $schema: $schema$1,
  $id: $id$1,
  title,
  definitions,
  type: type$1,
  properties: properties$1,
  "default": true
};
var definition_schema;
var hasRequiredDefinition_schema;
function requireDefinition_schema() {
  if (hasRequiredDefinition_schema) return definition_schema;
  hasRequiredDefinition_schema = 1;
  var metaSchema = require$$13;
  definition_schema = {
    $id: "https://github.com/ajv-validator/ajv/blob/master/lib/definition_schema.js",
    definitions: {
      simpleTypes: metaSchema.definitions.simpleTypes
    },
    type: "object",
    dependencies: {
      schema: ["validate"],
      $data: ["validate"],
      statements: ["inline"],
      valid: { not: { required: ["macro"] } }
    },
    properties: {
      type: metaSchema.properties.type,
      schema: { type: "boolean" },
      statements: { type: "boolean" },
      dependencies: {
        type: "array",
        items: { type: "string" }
      },
      metaSchema: { type: "object" },
      modifying: { type: "boolean" },
      valid: { type: "boolean" },
      $data: { type: "boolean" },
      async: { type: "boolean" },
      errors: {
        anyOf: [
          { type: "boolean" },
          { const: "full" }
        ]
      }
    }
  };
  return definition_schema;
}
var keyword;
var hasRequiredKeyword;
function requireKeyword() {
  if (hasRequiredKeyword) return keyword;
  hasRequiredKeyword = 1;
  var IDENTIFIER = /^[a-z_$][a-z0-9_$-]*$/i;
  var customRuleCode = requireCustom();
  var definitionSchema = requireDefinition_schema();
  keyword = {
    add: addKeyword,
    get: getKeyword,
    remove: removeKeyword,
    validate: validateKeyword
  };
  function addKeyword(keyword2, definition) {
    var RULES = this.RULES;
    if (RULES.keywords[keyword2])
      throw new Error("Keyword " + keyword2 + " is already defined");
    if (!IDENTIFIER.test(keyword2))
      throw new Error("Keyword " + keyword2 + " is not a valid identifier");
    if (definition) {
      this.validateKeyword(definition, true);
      var dataType = definition.type;
      if (Array.isArray(dataType)) {
        for (var i2 = 0; i2 < dataType.length; i2++)
          _addRule(keyword2, dataType[i2], definition);
      } else {
        _addRule(keyword2, dataType, definition);
      }
      var metaSchema = definition.metaSchema;
      if (metaSchema) {
        if (definition.$data && this._opts.$data) {
          metaSchema = {
            anyOf: [
              metaSchema,
              { "$ref": "https://raw.githubusercontent.com/ajv-validator/ajv/master/lib/refs/data.json#" }
            ]
          };
        }
        definition.validateSchema = this.compile(metaSchema, true);
      }
    }
    RULES.keywords[keyword2] = RULES.all[keyword2] = true;
    function _addRule(keyword3, dataType2, definition2) {
      var ruleGroup;
      for (var i3 = 0; i3 < RULES.length; i3++) {
        var rg = RULES[i3];
        if (rg.type == dataType2) {
          ruleGroup = rg;
          break;
        }
      }
      if (!ruleGroup) {
        ruleGroup = { type: dataType2, rules: [] };
        RULES.push(ruleGroup);
      }
      var rule = {
        keyword: keyword3,
        definition: definition2,
        custom: true,
        code: customRuleCode,
        implements: definition2.implements
      };
      ruleGroup.rules.push(rule);
      RULES.custom[keyword3] = rule;
    }
    return this;
  }
  function getKeyword(keyword2) {
    var rule = this.RULES.custom[keyword2];
    return rule ? rule.definition : this.RULES.keywords[keyword2] || false;
  }
  function removeKeyword(keyword2) {
    var RULES = this.RULES;
    delete RULES.keywords[keyword2];
    delete RULES.all[keyword2];
    delete RULES.custom[keyword2];
    for (var i2 = 0; i2 < RULES.length; i2++) {
      var rules2 = RULES[i2].rules;
      for (var j2 = 0; j2 < rules2.length; j2++) {
        if (rules2[j2].keyword == keyword2) {
          rules2.splice(j2, 1);
          break;
        }
      }
    }
    return this;
  }
  function validateKeyword(definition, throwError) {
    validateKeyword.errors = null;
    var v2 = this._validateKeyword = this._validateKeyword || this.compile(definitionSchema, true);
    if (v2(definition)) return true;
    validateKeyword.errors = v2.errors;
    if (throwError)
      throw new Error("custom keyword definition is invalid: " + this.errorsText(v2.errors));
    else
      return false;
  }
  return keyword;
}
const $schema = "http://json-schema.org/draft-07/schema#";
const $id = "https://raw.githubusercontent.com/ajv-validator/ajv/master/lib/refs/data.json#";
const description = "Meta-schema for $data reference (JSON Schema extension proposal)";
const type = "object";
const required = ["$data"];
const properties = { "$data": { "type": "string", "anyOf": [{ "format": "relative-json-pointer" }, { "format": "json-pointer" }] } };
const additionalProperties = false;
const require$$12 = {
  $schema,
  $id,
  description,
  type,
  required,
  properties,
  additionalProperties
};
var ajv;
var hasRequiredAjv;
function requireAjv() {
  if (hasRequiredAjv) return ajv;
  hasRequiredAjv = 1;
  var compileSchema = requireCompile(), resolve = requireResolve(), Cache = requireCache(), SchemaObject = requireSchema_obj(), stableStringify = requireFastJsonStableStringify(), formats = requireFormats(), rules2 = requireRules(), $dataMetaSchema = requireData(), util2 = requireUtil();
  ajv = Ajv2;
  Ajv2.prototype.validate = validate2;
  Ajv2.prototype.compile = compile;
  Ajv2.prototype.addSchema = addSchema;
  Ajv2.prototype.addMetaSchema = addMetaSchema;
  Ajv2.prototype.validateSchema = validateSchema;
  Ajv2.prototype.getSchema = getSchema;
  Ajv2.prototype.removeSchema = removeSchema;
  Ajv2.prototype.addFormat = addFormat2;
  Ajv2.prototype.errorsText = errorsText;
  Ajv2.prototype._addSchema = _addSchema;
  Ajv2.prototype._compile = _compile;
  Ajv2.prototype.compileAsync = requireAsync();
  var customKeyword = requireKeyword();
  Ajv2.prototype.addKeyword = customKeyword.add;
  Ajv2.prototype.getKeyword = customKeyword.get;
  Ajv2.prototype.removeKeyword = customKeyword.remove;
  Ajv2.prototype.validateKeyword = customKeyword.validate;
  var errorClasses = requireError_classes();
  Ajv2.ValidationError = errorClasses.Validation;
  Ajv2.MissingRefError = errorClasses.MissingRef;
  Ajv2.$dataMetaSchema = $dataMetaSchema;
  var META_SCHEMA_ID = "http://json-schema.org/draft-07/schema";
  var META_IGNORE_OPTIONS = ["removeAdditional", "useDefaults", "coerceTypes", "strictDefaults"];
  var META_SUPPORT_DATA = ["/properties"];
  function Ajv2(opts) {
    if (!(this instanceof Ajv2)) return new Ajv2(opts);
    opts = this._opts = util2.copy(opts) || {};
    setLogger(this);
    this._schemas = {};
    this._refs = {};
    this._fragments = {};
    this._formats = formats(opts.format);
    this._cache = opts.cache || new Cache();
    this._loadingSchemas = {};
    this._compilations = [];
    this.RULES = rules2();
    this._getId = chooseGetId(opts);
    opts.loopRequired = opts.loopRequired || Infinity;
    if (opts.errorDataPath == "property") opts._errorDataPathProperty = true;
    if (opts.serialize === void 0) opts.serialize = stableStringify;
    this._metaOpts = getMetaSchemaOptions(this);
    if (opts.formats) addInitialFormats(this);
    if (opts.keywords) addInitialKeywords(this);
    addDefaultMetaSchema(this);
    if (typeof opts.meta == "object") this.addMetaSchema(opts.meta);
    if (opts.nullable) this.addKeyword("nullable", { metaSchema: { type: "boolean" } });
    addInitialSchemas(this);
  }
  function validate2(schemaKeyRef, data2) {
    var v2;
    if (typeof schemaKeyRef == "string") {
      v2 = this.getSchema(schemaKeyRef);
      if (!v2) throw new Error('no schema with key or ref "' + schemaKeyRef + '"');
    } else {
      var schemaObj = this._addSchema(schemaKeyRef);
      v2 = schemaObj.validate || this._compile(schemaObj);
    }
    var valid = v2(data2);
    if (v2.$async !== true) this.errors = v2.errors;
    return valid;
  }
  function compile(schema, _meta) {
    var schemaObj = this._addSchema(schema, void 0, _meta);
    return schemaObj.validate || this._compile(schemaObj);
  }
  function addSchema(schema, key, _skipValidation, _meta) {
    if (Array.isArray(schema)) {
      for (var i2 = 0; i2 < schema.length; i2++) this.addSchema(schema[i2], void 0, _skipValidation, _meta);
      return this;
    }
    var id = this._getId(schema);
    if (id !== void 0 && typeof id != "string")
      throw new Error("schema id must be string");
    key = resolve.normalizeId(key || id);
    checkUnique(this, key);
    this._schemas[key] = this._addSchema(schema, _skipValidation, _meta, true);
    return this;
  }
  function addMetaSchema(schema, key, skipValidation) {
    this.addSchema(schema, key, skipValidation, true);
    return this;
  }
  function validateSchema(schema, throwOrLogError) {
    var $schema2 = schema.$schema;
    if ($schema2 !== void 0 && typeof $schema2 != "string")
      throw new Error("$schema must be a string");
    $schema2 = $schema2 || this._opts.defaultMeta || defaultMeta(this);
    if (!$schema2) {
      this.logger.warn("meta-schema not available");
      this.errors = null;
      return true;
    }
    var valid = this.validate($schema2, schema);
    if (!valid && throwOrLogError) {
      var message = "schema is invalid: " + this.errorsText();
      if (this._opts.validateSchema == "log") this.logger.error(message);
      else throw new Error(message);
    }
    return valid;
  }
  function defaultMeta(self) {
    var meta = self._opts.meta;
    self._opts.defaultMeta = typeof meta == "object" ? self._getId(meta) || meta : self.getSchema(META_SCHEMA_ID) ? META_SCHEMA_ID : void 0;
    return self._opts.defaultMeta;
  }
  function getSchema(keyRef) {
    var schemaObj = _getSchemaObj(this, keyRef);
    switch (typeof schemaObj) {
      case "object":
        return schemaObj.validate || this._compile(schemaObj);
      case "string":
        return this.getSchema(schemaObj);
      case "undefined":
        return _getSchemaFragment(this, keyRef);
    }
  }
  function _getSchemaFragment(self, ref2) {
    var res = resolve.schema.call(self, { schema: {} }, ref2);
    if (res) {
      var schema = res.schema, root = res.root, baseId = res.baseId;
      var v2 = compileSchema.call(self, schema, root, void 0, baseId);
      self._fragments[ref2] = new SchemaObject({
        ref: ref2,
        fragment: true,
        schema,
        root,
        baseId,
        validate: v2
      });
      return v2;
    }
  }
  function _getSchemaObj(self, keyRef) {
    keyRef = resolve.normalizeId(keyRef);
    return self._schemas[keyRef] || self._refs[keyRef] || self._fragments[keyRef];
  }
  function removeSchema(schemaKeyRef) {
    if (schemaKeyRef instanceof RegExp) {
      _removeAllSchemas(this, this._schemas, schemaKeyRef);
      _removeAllSchemas(this, this._refs, schemaKeyRef);
      return this;
    }
    switch (typeof schemaKeyRef) {
      case "undefined":
        _removeAllSchemas(this, this._schemas);
        _removeAllSchemas(this, this._refs);
        this._cache.clear();
        return this;
      case "string":
        var schemaObj = _getSchemaObj(this, schemaKeyRef);
        if (schemaObj) this._cache.del(schemaObj.cacheKey);
        delete this._schemas[schemaKeyRef];
        delete this._refs[schemaKeyRef];
        return this;
      case "object":
        var serialize = this._opts.serialize;
        var cacheKey = serialize ? serialize(schemaKeyRef) : schemaKeyRef;
        this._cache.del(cacheKey);
        var id = this._getId(schemaKeyRef);
        if (id) {
          id = resolve.normalizeId(id);
          delete this._schemas[id];
          delete this._refs[id];
        }
    }
    return this;
  }
  function _removeAllSchemas(self, schemas, regex) {
    for (var keyRef in schemas) {
      var schemaObj = schemas[keyRef];
      if (!schemaObj.meta && (!regex || regex.test(keyRef))) {
        self._cache.del(schemaObj.cacheKey);
        delete schemas[keyRef];
      }
    }
  }
  function _addSchema(schema, skipValidation, meta, shouldAddSchema) {
    if (typeof schema != "object" && typeof schema != "boolean")
      throw new Error("schema should be object or boolean");
    var serialize = this._opts.serialize;
    var cacheKey = serialize ? serialize(schema) : schema;
    var cached = this._cache.get(cacheKey);
    if (cached) return cached;
    shouldAddSchema = shouldAddSchema || this._opts.addUsedSchema !== false;
    var id = resolve.normalizeId(this._getId(schema));
    if (id && shouldAddSchema) checkUnique(this, id);
    var willValidate = this._opts.validateSchema !== false && !skipValidation;
    var recursiveMeta;
    if (willValidate && !(recursiveMeta = id && id == resolve.normalizeId(schema.$schema)))
      this.validateSchema(schema, true);
    var localRefs = resolve.ids.call(this, schema);
    var schemaObj = new SchemaObject({
      id,
      schema,
      localRefs,
      cacheKey,
      meta
    });
    if (id[0] != "#" && shouldAddSchema) this._refs[id] = schemaObj;
    this._cache.put(cacheKey, schemaObj);
    if (willValidate && recursiveMeta) this.validateSchema(schema, true);
    return schemaObj;
  }
  function _compile(schemaObj, root) {
    if (schemaObj.compiling) {
      schemaObj.validate = callValidate;
      callValidate.schema = schemaObj.schema;
      callValidate.errors = null;
      callValidate.root = root ? root : callValidate;
      if (schemaObj.schema.$async === true)
        callValidate.$async = true;
      return callValidate;
    }
    schemaObj.compiling = true;
    var currentOpts;
    if (schemaObj.meta) {
      currentOpts = this._opts;
      this._opts = this._metaOpts;
    }
    var v2;
    try {
      v2 = compileSchema.call(this, schemaObj.schema, root, schemaObj.localRefs);
    } catch (e2) {
      delete schemaObj.validate;
      throw e2;
    } finally {
      schemaObj.compiling = false;
      if (schemaObj.meta) this._opts = currentOpts;
    }
    schemaObj.validate = v2;
    schemaObj.refs = v2.refs;
    schemaObj.refVal = v2.refVal;
    schemaObj.root = v2.root;
    return v2;
    function callValidate() {
      var _validate = schemaObj.validate;
      var result = _validate.apply(this, arguments);
      callValidate.errors = _validate.errors;
      return result;
    }
  }
  function chooseGetId(opts) {
    switch (opts.schemaId) {
      case "auto":
        return _get$IdOrId;
      case "id":
        return _getId;
      default:
        return _get$Id;
    }
  }
  function _getId(schema) {
    if (schema.$id) this.logger.warn("schema $id ignored", schema.$id);
    return schema.id;
  }
  function _get$Id(schema) {
    if (schema.id) this.logger.warn("schema id ignored", schema.id);
    return schema.$id;
  }
  function _get$IdOrId(schema) {
    if (schema.$id && schema.id && schema.$id != schema.id)
      throw new Error("schema $id is different from id");
    return schema.$id || schema.id;
  }
  function errorsText(errors, options) {
    errors = errors || this.errors;
    if (!errors) return "No errors";
    options = options || {};
    var separator = options.separator === void 0 ? ", " : options.separator;
    var dataVar = options.dataVar === void 0 ? "data" : options.dataVar;
    var text = "";
    for (var i2 = 0; i2 < errors.length; i2++) {
      var e2 = errors[i2];
      if (e2) text += dataVar + e2.dataPath + " " + e2.message + separator;
    }
    return text.slice(0, -separator.length);
  }
  function addFormat2(name, format2) {
    if (typeof format2 == "string") format2 = new RegExp(format2);
    this._formats[name] = format2;
    return this;
  }
  function addDefaultMetaSchema(self) {
    var $dataSchema;
    if (self._opts.$data) {
      $dataSchema = require$$12;
      self.addMetaSchema($dataSchema, $dataSchema.$id, true);
    }
    if (self._opts.meta === false) return;
    var metaSchema = require$$13;
    if (self._opts.$data) metaSchema = $dataMetaSchema(metaSchema, META_SUPPORT_DATA);
    self.addMetaSchema(metaSchema, META_SCHEMA_ID, true);
    self._refs["http://json-schema.org/schema"] = META_SCHEMA_ID;
  }
  function addInitialSchemas(self) {
    var optsSchemas = self._opts.schemas;
    if (!optsSchemas) return;
    if (Array.isArray(optsSchemas)) self.addSchema(optsSchemas);
    else for (var key in optsSchemas) self.addSchema(optsSchemas[key], key);
  }
  function addInitialFormats(self) {
    for (var name in self._opts.formats) {
      var format2 = self._opts.formats[name];
      self.addFormat(name, format2);
    }
  }
  function addInitialKeywords(self) {
    for (var name in self._opts.keywords) {
      var keyword2 = self._opts.keywords[name];
      self.addKeyword(name, keyword2);
    }
  }
  function checkUnique(self, id) {
    if (self._schemas[id] || self._refs[id])
      throw new Error('schema with key or id "' + id + '" already exists');
  }
  function getMetaSchemaOptions(self) {
    var metaOpts = util2.copy(self._opts);
    for (var i2 = 0; i2 < META_IGNORE_OPTIONS.length; i2++)
      delete metaOpts[META_IGNORE_OPTIONS[i2]];
    return metaOpts;
  }
  function setLogger(self) {
    var logger = self._opts.logger;
    if (logger === false) {
      self.logger = { log: noop, warn: noop, error: noop };
    } else {
      if (logger === void 0) logger = console;
      if (!(typeof logger == "object" && logger.log && logger.warn && logger.error))
        throw new Error("logger must implement log, warn and error methods");
      self.logger = logger;
    }
  }
  function noop() {
  }
  return ajv;
}
var ajvExports = requireAjv();
const Ajv = /* @__PURE__ */ getDefaultExportFromCjs(ajvExports);
class Server extends Protocol {
  /**
   * Initializes this server with the given name and version information.
   */
  constructor(_serverInfo, options) {
    var _a;
    super(options);
    this._serverInfo = _serverInfo;
    this._loggingLevels = /* @__PURE__ */ new Map();
    this.LOG_LEVEL_SEVERITY = new Map(LoggingLevelSchema.options.map((level, index) => [level, index]));
    this.isMessageIgnored = (level, sessionId) => {
      const currentLevel = this._loggingLevels.get(sessionId);
      return currentLevel ? this.LOG_LEVEL_SEVERITY.get(level) < this.LOG_LEVEL_SEVERITY.get(currentLevel) : false;
    };
    this._capabilities = (_a = options === null || options === void 0 ? void 0 : options.capabilities) !== null && _a !== void 0 ? _a : {};
    this._instructions = options === null || options === void 0 ? void 0 : options.instructions;
    this.setRequestHandler(InitializeRequestSchema, (request) => this._oninitialize(request));
    this.setNotificationHandler(InitializedNotificationSchema, () => {
      var _a2;
      return (_a2 = this.oninitialized) === null || _a2 === void 0 ? void 0 : _a2.call(this);
    });
    if (this._capabilities.logging) {
      this.setRequestHandler(SetLevelRequestSchema, async (request, extra) => {
        var _a2;
        const transportSessionId = extra.sessionId || ((_a2 = extra.requestInfo) === null || _a2 === void 0 ? void 0 : _a2.headers["mcp-session-id"]) || void 0;
        const { level } = request.params;
        const parseResult = LoggingLevelSchema.safeParse(level);
        if (parseResult.success) {
          this._loggingLevels.set(transportSessionId, parseResult.data);
        }
        return {};
      });
    }
  }
  /**
   * Registers new capabilities. This can only be called before connecting to a transport.
   *
   * The new capabilities will be merged with any existing capabilities previously given (e.g., at initialization).
   */
  registerCapabilities(capabilities) {
    if (this.transport) {
      throw new Error("Cannot register capabilities after connecting to transport");
    }
    this._capabilities = mergeCapabilities(this._capabilities, capabilities);
  }
  assertCapabilityForMethod(method) {
    var _a, _b, _c;
    switch (method) {
      case "sampling/createMessage":
        if (!((_a = this._clientCapabilities) === null || _a === void 0 ? void 0 : _a.sampling)) {
          throw new Error(`Client does not support sampling (required for ${method})`);
        }
        break;
      case "elicitation/create":
        if (!((_b = this._clientCapabilities) === null || _b === void 0 ? void 0 : _b.elicitation)) {
          throw new Error(`Client does not support elicitation (required for ${method})`);
        }
        break;
      case "roots/list":
        if (!((_c = this._clientCapabilities) === null || _c === void 0 ? void 0 : _c.roots)) {
          throw new Error(`Client does not support listing roots (required for ${method})`);
        }
        break;
    }
  }
  assertNotificationCapability(method) {
    switch (method) {
      case "notifications/message":
        if (!this._capabilities.logging) {
          throw new Error(`Server does not support logging (required for ${method})`);
        }
        break;
      case "notifications/resources/updated":
      case "notifications/resources/list_changed":
        if (!this._capabilities.resources) {
          throw new Error(`Server does not support notifying about resources (required for ${method})`);
        }
        break;
      case "notifications/tools/list_changed":
        if (!this._capabilities.tools) {
          throw new Error(`Server does not support notifying of tool list changes (required for ${method})`);
        }
        break;
      case "notifications/prompts/list_changed":
        if (!this._capabilities.prompts) {
          throw new Error(`Server does not support notifying of prompt list changes (required for ${method})`);
        }
        break;
    }
  }
  assertRequestHandlerCapability(method) {
    switch (method) {
      case "sampling/createMessage":
        if (!this._capabilities.sampling) {
          throw new Error(`Server does not support sampling (required for ${method})`);
        }
        break;
      case "logging/setLevel":
        if (!this._capabilities.logging) {
          throw new Error(`Server does not support logging (required for ${method})`);
        }
        break;
      case "prompts/get":
      case "prompts/list":
        if (!this._capabilities.prompts) {
          throw new Error(`Server does not support prompts (required for ${method})`);
        }
        break;
      case "resources/list":
      case "resources/templates/list":
      case "resources/read":
        if (!this._capabilities.resources) {
          throw new Error(`Server does not support resources (required for ${method})`);
        }
        break;
      case "tools/call":
      case "tools/list":
        if (!this._capabilities.tools) {
          throw new Error(`Server does not support tools (required for ${method})`);
        }
        break;
    }
  }
  async _oninitialize(request) {
    const requestedVersion = request.params.protocolVersion;
    this._clientCapabilities = request.params.capabilities;
    this._clientVersion = request.params.clientInfo;
    const protocolVersion = SUPPORTED_PROTOCOL_VERSIONS.includes(requestedVersion) ? requestedVersion : LATEST_PROTOCOL_VERSION;
    return {
      protocolVersion,
      capabilities: this.getCapabilities(),
      serverInfo: this._serverInfo,
      ...this._instructions && { instructions: this._instructions }
    };
  }
  /**
   * After initialization has completed, this will be populated with the client's reported capabilities.
   */
  getClientCapabilities() {
    return this._clientCapabilities;
  }
  /**
   * After initialization has completed, this will be populated with information about the client's name and version.
   */
  getClientVersion() {
    return this._clientVersion;
  }
  getCapabilities() {
    return this._capabilities;
  }
  async ping() {
    return this.request({ method: "ping" }, EmptyResultSchema);
  }
  async createMessage(params, options) {
    return this.request({ method: "sampling/createMessage", params }, CreateMessageResultSchema, options);
  }
  async elicitInput(params, options) {
    const result = await this.request({ method: "elicitation/create", params }, ElicitResultSchema, options);
    if (result.action === "accept" && result.content) {
      try {
        const ajv2 = new Ajv();
        const validate2 = ajv2.compile(params.requestedSchema);
        const isValid2 = validate2(result.content);
        if (!isValid2) {
          throw new McpError(ErrorCode.InvalidParams, `Elicitation response content does not match requested schema: ${ajv2.errorsText(validate2.errors)}`);
        }
      } catch (error) {
        if (error instanceof McpError) {
          throw error;
        }
        throw new McpError(ErrorCode.InternalError, `Error validating elicitation response: ${error}`);
      }
    }
    return result;
  }
  async listRoots(params, options) {
    return this.request({ method: "roots/list", params }, ListRootsResultSchema, options);
  }
  /**
   * Sends a logging message to the client, if connected.
   * Note: You only need to send the parameters object, not the entire JSON RPC message
   * @see LoggingMessageNotification
   * @param params
   * @param sessionId optional for stateless and backward compatibility
   */
  async sendLoggingMessage(params, sessionId) {
    if (this._capabilities.logging) {
      if (!this.isMessageIgnored(params.level, sessionId)) {
        return this.notification({ method: "notifications/message", params });
      }
    }
  }
  async sendResourceUpdated(params) {
    return this.notification({
      method: "notifications/resources/updated",
      params
    });
  }
  async sendResourceListChanged() {
    return this.notification({
      method: "notifications/resources/list_changed"
    });
  }
  async sendToolListChanged() {
    return this.notification({ method: "notifications/tools/list_changed" });
  }
  async sendPromptListChanged() {
    return this.notification({ method: "notifications/prompts/list_changed" });
  }
}
const ignoreOverride = Symbol("Let zodToJsonSchema decide on which parser to use");
const defaultOptions = {
  name: void 0,
  $refStrategy: "root",
  basePath: ["#"],
  effectStrategy: "input",
  pipeStrategy: "all",
  dateStrategy: "format:date-time",
  mapStrategy: "entries",
  removeAdditionalStrategy: "passthrough",
  allowedAdditionalProperties: true,
  rejectedAdditionalProperties: false,
  definitionPath: "definitions",
  target: "jsonSchema7",
  strictUnions: false,
  definitions: {},
  errorMessages: false,
  markdownDescription: false,
  patternStrategy: "escape",
  applyRegexFlags: false,
  emailStrategy: "format:email",
  base64Strategy: "contentEncoding:base64",
  nameStrategy: "ref",
  openAiAnyTypeName: "OpenAiAnyType"
};
const getDefaultOptions = (options) => typeof options === "string" ? {
  ...defaultOptions,
  name: options
} : {
  ...defaultOptions,
  ...options
};
const getRefs = (options) => {
  const _options = getDefaultOptions(options);
  const currentPath = _options.name !== void 0 ? [..._options.basePath, _options.definitionPath, _options.name] : _options.basePath;
  return {
    ..._options,
    flags: { hasReferencedOpenAiAnyType: false },
    currentPath,
    propertyPath: void 0,
    seen: new Map(Object.entries(_options.definitions).map(([name, def]) => [
      def._def,
      {
        def: def._def,
        path: [..._options.basePath, _options.definitionPath, name],
        // Resolution of references will be forced even though seen, so it's ok that the schema is undefined here for now.
        jsonSchema: void 0
      }
    ]))
  };
};
function addErrorMessage(res, key, errorMessage, refs) {
  if (!(refs == null ? void 0 : refs.errorMessages))
    return;
  if (errorMessage) {
    res.errorMessage = {
      ...res.errorMessage,
      [key]: errorMessage
    };
  }
}
function setResponseValueAndErrors(res, key, value, errorMessage, refs) {
  res[key] = value;
  addErrorMessage(res, key, errorMessage, refs);
}
const getRelativePath = (pathA, pathB) => {
  let i2 = 0;
  for (; i2 < pathA.length && i2 < pathB.length; i2++) {
    if (pathA[i2] !== pathB[i2])
      break;
  }
  return [(pathA.length - i2).toString(), ...pathB.slice(i2)].join("/");
};
function parseAnyDef(refs) {
  if (refs.target !== "openAi") {
    return {};
  }
  const anyDefinitionPath = [
    ...refs.basePath,
    refs.definitionPath,
    refs.openAiAnyTypeName
  ];
  refs.flags.hasReferencedOpenAiAnyType = true;
  return {
    $ref: refs.$refStrategy === "relative" ? getRelativePath(anyDefinitionPath, refs.currentPath) : anyDefinitionPath.join("/")
  };
}
function parseArrayDef(def, refs) {
  var _a, _b, _c;
  const res = {
    type: "array"
  };
  if (((_a = def.type) == null ? void 0 : _a._def) && ((_c = (_b = def.type) == null ? void 0 : _b._def) == null ? void 0 : _c.typeName) !== ZodFirstPartyTypeKind.ZodAny) {
    res.items = parseDef(def.type._def, {
      ...refs,
      currentPath: [...refs.currentPath, "items"]
    });
  }
  if (def.minLength) {
    setResponseValueAndErrors(res, "minItems", def.minLength.value, def.minLength.message, refs);
  }
  if (def.maxLength) {
    setResponseValueAndErrors(res, "maxItems", def.maxLength.value, def.maxLength.message, refs);
  }
  if (def.exactLength) {
    setResponseValueAndErrors(res, "minItems", def.exactLength.value, def.exactLength.message, refs);
    setResponseValueAndErrors(res, "maxItems", def.exactLength.value, def.exactLength.message, refs);
  }
  return res;
}
function parseBigintDef(def, refs) {
  const res = {
    type: "integer",
    format: "int64"
  };
  if (!def.checks)
    return res;
  for (const check of def.checks) {
    switch (check.kind) {
      case "min":
        if (refs.target === "jsonSchema7") {
          if (check.inclusive) {
            setResponseValueAndErrors(res, "minimum", check.value, check.message, refs);
          } else {
            setResponseValueAndErrors(res, "exclusiveMinimum", check.value, check.message, refs);
          }
        } else {
          if (!check.inclusive) {
            res.exclusiveMinimum = true;
          }
          setResponseValueAndErrors(res, "minimum", check.value, check.message, refs);
        }
        break;
      case "max":
        if (refs.target === "jsonSchema7") {
          if (check.inclusive) {
            setResponseValueAndErrors(res, "maximum", check.value, check.message, refs);
          } else {
            setResponseValueAndErrors(res, "exclusiveMaximum", check.value, check.message, refs);
          }
        } else {
          if (!check.inclusive) {
            res.exclusiveMaximum = true;
          }
          setResponseValueAndErrors(res, "maximum", check.value, check.message, refs);
        }
        break;
      case "multipleOf":
        setResponseValueAndErrors(res, "multipleOf", check.value, check.message, refs);
        break;
    }
  }
  return res;
}
function parseBooleanDef() {
  return {
    type: "boolean"
  };
}
function parseBrandedDef(_def, refs) {
  return parseDef(_def.type._def, refs);
}
const parseCatchDef = (def, refs) => {
  return parseDef(def.innerType._def, refs);
};
function parseDateDef(def, refs, overrideDateStrategy) {
  const strategy = overrideDateStrategy ?? refs.dateStrategy;
  if (Array.isArray(strategy)) {
    return {
      anyOf: strategy.map((item, i2) => parseDateDef(def, refs, item))
    };
  }
  switch (strategy) {
    case "string":
    case "format:date-time":
      return {
        type: "string",
        format: "date-time"
      };
    case "format:date":
      return {
        type: "string",
        format: "date"
      };
    case "integer":
      return integerDateParser(def, refs);
  }
}
const integerDateParser = (def, refs) => {
  const res = {
    type: "integer",
    format: "unix-time"
  };
  if (refs.target === "openApi3") {
    return res;
  }
  for (const check of def.checks) {
    switch (check.kind) {
      case "min":
        setResponseValueAndErrors(
          res,
          "minimum",
          check.value,
          // This is in milliseconds
          check.message,
          refs
        );
        break;
      case "max":
        setResponseValueAndErrors(
          res,
          "maximum",
          check.value,
          // This is in milliseconds
          check.message,
          refs
        );
        break;
    }
  }
  return res;
};
function parseDefaultDef(_def, refs) {
  return {
    ...parseDef(_def.innerType._def, refs),
    default: _def.defaultValue()
  };
}
function parseEffectsDef(_def, refs) {
  return refs.effectStrategy === "input" ? parseDef(_def.schema._def, refs) : parseAnyDef(refs);
}
function parseEnumDef(def) {
  return {
    type: "string",
    enum: Array.from(def.values)
  };
}
const isJsonSchema7AllOfType = (type2) => {
  if ("type" in type2 && type2.type === "string")
    return false;
  return "allOf" in type2;
};
function parseIntersectionDef(def, refs) {
  const allOf2 = [
    parseDef(def.left._def, {
      ...refs,
      currentPath: [...refs.currentPath, "allOf", "0"]
    }),
    parseDef(def.right._def, {
      ...refs,
      currentPath: [...refs.currentPath, "allOf", "1"]
    })
  ].filter((x2) => !!x2);
  let unevaluatedProperties = refs.target === "jsonSchema2019-09" ? { unevaluatedProperties: false } : void 0;
  const mergedAllOf = [];
  allOf2.forEach((schema) => {
    if (isJsonSchema7AllOfType(schema)) {
      mergedAllOf.push(...schema.allOf);
      if (schema.unevaluatedProperties === void 0) {
        unevaluatedProperties = void 0;
      }
    } else {
      let nestedSchema = schema;
      if ("additionalProperties" in schema && schema.additionalProperties === false) {
        const { additionalProperties: additionalProperties2, ...rest } = schema;
        nestedSchema = rest;
      } else {
        unevaluatedProperties = void 0;
      }
      mergedAllOf.push(nestedSchema);
    }
  });
  return mergedAllOf.length ? {
    allOf: mergedAllOf,
    ...unevaluatedProperties
  } : void 0;
}
function parseLiteralDef(def, refs) {
  const parsedType = typeof def.value;
  if (parsedType !== "bigint" && parsedType !== "number" && parsedType !== "boolean" && parsedType !== "string") {
    return {
      type: Array.isArray(def.value) ? "array" : "object"
    };
  }
  if (refs.target === "openApi3") {
    return {
      type: parsedType === "bigint" ? "integer" : parsedType,
      enum: [def.value]
    };
  }
  return {
    type: parsedType === "bigint" ? "integer" : parsedType,
    const: def.value
  };
}
let emojiRegex = void 0;
const zodPatterns = {
  /**
   * `c` was changed to `[cC]` to replicate /i flag
   */
  cuid: /^[cC][^\s-]{8,}$/,
  cuid2: /^[0-9a-z]+$/,
  ulid: /^[0-9A-HJKMNP-TV-Z]{26}$/,
  /**
   * `a-z` was added to replicate /i flag
   */
  email: /^(?!\.)(?!.*\.\.)([a-zA-Z0-9_'+\-\.]*)[a-zA-Z0-9_+-]@([a-zA-Z0-9][a-zA-Z0-9\-]*\.)+[a-zA-Z]{2,}$/,
  /**
   * Constructed a valid Unicode RegExp
   *
   * Lazily instantiate since this type of regex isn't supported
   * in all envs (e.g. React Native).
   *
   * See:
   * https://github.com/colinhacks/zod/issues/2433
   * Fix in Zod:
   * https://github.com/colinhacks/zod/commit/9340fd51e48576a75adc919bff65dbc4a5d4c99b
   */
  emoji: () => {
    if (emojiRegex === void 0) {
      emojiRegex = RegExp("^(\\p{Extended_Pictographic}|\\p{Emoji_Component})+$", "u");
    }
    return emojiRegex;
  },
  /**
   * Unused
   */
  uuid: /^[0-9a-fA-F]{8}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{12}$/,
  /**
   * Unused
   */
  ipv4: /^(?:(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\.){3}(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])$/,
  ipv4Cidr: /^(?:(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\.){3}(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\/(3[0-2]|[12]?[0-9])$/,
  /**
   * Unused
   */
  ipv6: /^(([a-f0-9]{1,4}:){7}|::([a-f0-9]{1,4}:){0,6}|([a-f0-9]{1,4}:){1}:([a-f0-9]{1,4}:){0,5}|([a-f0-9]{1,4}:){2}:([a-f0-9]{1,4}:){0,4}|([a-f0-9]{1,4}:){3}:([a-f0-9]{1,4}:){0,3}|([a-f0-9]{1,4}:){4}:([a-f0-9]{1,4}:){0,2}|([a-f0-9]{1,4}:){5}:([a-f0-9]{1,4}:){0,1})([a-f0-9]{1,4}|(((25[0-5])|(2[0-4][0-9])|(1[0-9]{2})|([0-9]{1,2}))\.){3}((25[0-5])|(2[0-4][0-9])|(1[0-9]{2})|([0-9]{1,2})))$/,
  ipv6Cidr: /^(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]))\/(12[0-8]|1[01][0-9]|[1-9]?[0-9])$/,
  base64: /^([0-9a-zA-Z+/]{4})*(([0-9a-zA-Z+/]{2}==)|([0-9a-zA-Z+/]{3}=))?$/,
  base64url: /^([0-9a-zA-Z-_]{4})*(([0-9a-zA-Z-_]{2}(==)?)|([0-9a-zA-Z-_]{3}(=)?))?$/,
  nanoid: /^[a-zA-Z0-9_-]{21}$/,
  jwt: /^[A-Za-z0-9-_]+\.[A-Za-z0-9-_]+\.[A-Za-z0-9-_]*$/
};
function parseStringDef(def, refs) {
  const res = {
    type: "string"
  };
  if (def.checks) {
    for (const check of def.checks) {
      switch (check.kind) {
        case "min":
          setResponseValueAndErrors(res, "minLength", typeof res.minLength === "number" ? Math.max(res.minLength, check.value) : check.value, check.message, refs);
          break;
        case "max":
          setResponseValueAndErrors(res, "maxLength", typeof res.maxLength === "number" ? Math.min(res.maxLength, check.value) : check.value, check.message, refs);
          break;
        case "email":
          switch (refs.emailStrategy) {
            case "format:email":
              addFormat(res, "email", check.message, refs);
              break;
            case "format:idn-email":
              addFormat(res, "idn-email", check.message, refs);
              break;
            case "pattern:zod":
              addPattern(res, zodPatterns.email, check.message, refs);
              break;
          }
          break;
        case "url":
          addFormat(res, "uri", check.message, refs);
          break;
        case "uuid":
          addFormat(res, "uuid", check.message, refs);
          break;
        case "regex":
          addPattern(res, check.regex, check.message, refs);
          break;
        case "cuid":
          addPattern(res, zodPatterns.cuid, check.message, refs);
          break;
        case "cuid2":
          addPattern(res, zodPatterns.cuid2, check.message, refs);
          break;
        case "startsWith":
          addPattern(res, RegExp(`^${escapeLiteralCheckValue(check.value, refs)}`), check.message, refs);
          break;
        case "endsWith":
          addPattern(res, RegExp(`${escapeLiteralCheckValue(check.value, refs)}$`), check.message, refs);
          break;
        case "datetime":
          addFormat(res, "date-time", check.message, refs);
          break;
        case "date":
          addFormat(res, "date", check.message, refs);
          break;
        case "time":
          addFormat(res, "time", check.message, refs);
          break;
        case "duration":
          addFormat(res, "duration", check.message, refs);
          break;
        case "length":
          setResponseValueAndErrors(res, "minLength", typeof res.minLength === "number" ? Math.max(res.minLength, check.value) : check.value, check.message, refs);
          setResponseValueAndErrors(res, "maxLength", typeof res.maxLength === "number" ? Math.min(res.maxLength, check.value) : check.value, check.message, refs);
          break;
        case "includes": {
          addPattern(res, RegExp(escapeLiteralCheckValue(check.value, refs)), check.message, refs);
          break;
        }
        case "ip": {
          if (check.version !== "v6") {
            addFormat(res, "ipv4", check.message, refs);
          }
          if (check.version !== "v4") {
            addFormat(res, "ipv6", check.message, refs);
          }
          break;
        }
        case "base64url":
          addPattern(res, zodPatterns.base64url, check.message, refs);
          break;
        case "jwt":
          addPattern(res, zodPatterns.jwt, check.message, refs);
          break;
        case "cidr": {
          if (check.version !== "v6") {
            addPattern(res, zodPatterns.ipv4Cidr, check.message, refs);
          }
          if (check.version !== "v4") {
            addPattern(res, zodPatterns.ipv6Cidr, check.message, refs);
          }
          break;
        }
        case "emoji":
          addPattern(res, zodPatterns.emoji(), check.message, refs);
          break;
        case "ulid": {
          addPattern(res, zodPatterns.ulid, check.message, refs);
          break;
        }
        case "base64": {
          switch (refs.base64Strategy) {
            case "format:binary": {
              addFormat(res, "binary", check.message, refs);
              break;
            }
            case "contentEncoding:base64": {
              setResponseValueAndErrors(res, "contentEncoding", "base64", check.message, refs);
              break;
            }
            case "pattern:zod": {
              addPattern(res, zodPatterns.base64, check.message, refs);
              break;
            }
          }
          break;
        }
        case "nanoid": {
          addPattern(res, zodPatterns.nanoid, check.message, refs);
        }
      }
    }
  }
  return res;
}
function escapeLiteralCheckValue(literal, refs) {
  return refs.patternStrategy === "escape" ? escapeNonAlphaNumeric(literal) : literal;
}
const ALPHA_NUMERIC = new Set("ABCDEFGHIJKLMNOPQRSTUVXYZabcdefghijklmnopqrstuvxyz0123456789");
function escapeNonAlphaNumeric(source) {
  let result = "";
  for (let i2 = 0; i2 < source.length; i2++) {
    if (!ALPHA_NUMERIC.has(source[i2])) {
      result += "\\";
    }
    result += source[i2];
  }
  return result;
}
function addFormat(schema, value, message, refs) {
  var _a;
  if (schema.format || ((_a = schema.anyOf) == null ? void 0 : _a.some((x2) => x2.format))) {
    if (!schema.anyOf) {
      schema.anyOf = [];
    }
    if (schema.format) {
      schema.anyOf.push({
        format: schema.format,
        ...schema.errorMessage && refs.errorMessages && {
          errorMessage: { format: schema.errorMessage.format }
        }
      });
      delete schema.format;
      if (schema.errorMessage) {
        delete schema.errorMessage.format;
        if (Object.keys(schema.errorMessage).length === 0) {
          delete schema.errorMessage;
        }
      }
    }
    schema.anyOf.push({
      format: value,
      ...message && refs.errorMessages && { errorMessage: { format: message } }
    });
  } else {
    setResponseValueAndErrors(schema, "format", value, message, refs);
  }
}
function addPattern(schema, regex, message, refs) {
  var _a;
  if (schema.pattern || ((_a = schema.allOf) == null ? void 0 : _a.some((x2) => x2.pattern))) {
    if (!schema.allOf) {
      schema.allOf = [];
    }
    if (schema.pattern) {
      schema.allOf.push({
        pattern: schema.pattern,
        ...schema.errorMessage && refs.errorMessages && {
          errorMessage: { pattern: schema.errorMessage.pattern }
        }
      });
      delete schema.pattern;
      if (schema.errorMessage) {
        delete schema.errorMessage.pattern;
        if (Object.keys(schema.errorMessage).length === 0) {
          delete schema.errorMessage;
        }
      }
    }
    schema.allOf.push({
      pattern: stringifyRegExpWithFlags(regex, refs),
      ...message && refs.errorMessages && { errorMessage: { pattern: message } }
    });
  } else {
    setResponseValueAndErrors(schema, "pattern", stringifyRegExpWithFlags(regex, refs), message, refs);
  }
}
function stringifyRegExpWithFlags(regex, refs) {
  var _a;
  if (!refs.applyRegexFlags || !regex.flags) {
    return regex.source;
  }
  const flags = {
    i: regex.flags.includes("i"),
    m: regex.flags.includes("m"),
    s: regex.flags.includes("s")
    // `.` matches newlines
  };
  const source = flags.i ? regex.source.toLowerCase() : regex.source;
  let pattern2 = "";
  let isEscaped = false;
  let inCharGroup = false;
  let inCharRange = false;
  for (let i2 = 0; i2 < source.length; i2++) {
    if (isEscaped) {
      pattern2 += source[i2];
      isEscaped = false;
      continue;
    }
    if (flags.i) {
      if (inCharGroup) {
        if (source[i2].match(/[a-z]/)) {
          if (inCharRange) {
            pattern2 += source[i2];
            pattern2 += `${source[i2 - 2]}-${source[i2]}`.toUpperCase();
            inCharRange = false;
          } else if (source[i2 + 1] === "-" && ((_a = source[i2 + 2]) == null ? void 0 : _a.match(/[a-z]/))) {
            pattern2 += source[i2];
            inCharRange = true;
          } else {
            pattern2 += `${source[i2]}${source[i2].toUpperCase()}`;
          }
          continue;
        }
      } else if (source[i2].match(/[a-z]/)) {
        pattern2 += `[${source[i2]}${source[i2].toUpperCase()}]`;
        continue;
      }
    }
    if (flags.m) {
      if (source[i2] === "^") {
        pattern2 += `(^|(?<=[\r
]))`;
        continue;
      } else if (source[i2] === "$") {
        pattern2 += `($|(?=[\r
]))`;
        continue;
      }
    }
    if (flags.s && source[i2] === ".") {
      pattern2 += inCharGroup ? `${source[i2]}\r
` : `[${source[i2]}\r
]`;
      continue;
    }
    pattern2 += source[i2];
    if (source[i2] === "\\") {
      isEscaped = true;
    } else if (inCharGroup && source[i2] === "]") {
      inCharGroup = false;
    } else if (!inCharGroup && source[i2] === "[") {
      inCharGroup = true;
    }
  }
  try {
    new RegExp(pattern2);
  } catch {
    console.warn(`Could not convert regex pattern at ${refs.currentPath.join("/")} to a flag-independent form! Falling back to the flag-ignorant source`);
    return regex.source;
  }
  return pattern2;
}
function parseRecordDef(def, refs) {
  var _a, _b, _c, _d, _e2, _f;
  if (refs.target === "openAi") {
    console.warn("Warning: OpenAI may not support records in schemas! Try an array of key-value pairs instead.");
  }
  if (refs.target === "openApi3" && ((_a = def.keyType) == null ? void 0 : _a._def.typeName) === ZodFirstPartyTypeKind.ZodEnum) {
    return {
      type: "object",
      required: def.keyType._def.values,
      properties: def.keyType._def.values.reduce((acc, key) => ({
        ...acc,
        [key]: parseDef(def.valueType._def, {
          ...refs,
          currentPath: [...refs.currentPath, "properties", key]
        }) ?? parseAnyDef(refs)
      }), {}),
      additionalProperties: refs.rejectedAdditionalProperties
    };
  }
  const schema = {
    type: "object",
    additionalProperties: parseDef(def.valueType._def, {
      ...refs,
      currentPath: [...refs.currentPath, "additionalProperties"]
    }) ?? refs.allowedAdditionalProperties
  };
  if (refs.target === "openApi3") {
    return schema;
  }
  if (((_b = def.keyType) == null ? void 0 : _b._def.typeName) === ZodFirstPartyTypeKind.ZodString && ((_c = def.keyType._def.checks) == null ? void 0 : _c.length)) {
    const { type: type2, ...keyType } = parseStringDef(def.keyType._def, refs);
    return {
      ...schema,
      propertyNames: keyType
    };
  } else if (((_d = def.keyType) == null ? void 0 : _d._def.typeName) === ZodFirstPartyTypeKind.ZodEnum) {
    return {
      ...schema,
      propertyNames: {
        enum: def.keyType._def.values
      }
    };
  } else if (((_e2 = def.keyType) == null ? void 0 : _e2._def.typeName) === ZodFirstPartyTypeKind.ZodBranded && def.keyType._def.type._def.typeName === ZodFirstPartyTypeKind.ZodString && ((_f = def.keyType._def.type._def.checks) == null ? void 0 : _f.length)) {
    const { type: type2, ...keyType } = parseBrandedDef(def.keyType._def, refs);
    return {
      ...schema,
      propertyNames: keyType
    };
  }
  return schema;
}
function parseMapDef(def, refs) {
  if (refs.mapStrategy === "record") {
    return parseRecordDef(def, refs);
  }
  const keys = parseDef(def.keyType._def, {
    ...refs,
    currentPath: [...refs.currentPath, "items", "items", "0"]
  }) || parseAnyDef(refs);
  const values = parseDef(def.valueType._def, {
    ...refs,
    currentPath: [...refs.currentPath, "items", "items", "1"]
  }) || parseAnyDef(refs);
  return {
    type: "array",
    maxItems: 125,
    items: {
      type: "array",
      items: [keys, values],
      minItems: 2,
      maxItems: 2
    }
  };
}
function parseNativeEnumDef(def) {
  const object = def.values;
  const actualKeys = Object.keys(def.values).filter((key) => {
    return typeof object[object[key]] !== "number";
  });
  const actualValues = actualKeys.map((key) => object[key]);
  const parsedTypes = Array.from(new Set(actualValues.map((values) => typeof values)));
  return {
    type: parsedTypes.length === 1 ? parsedTypes[0] === "string" ? "string" : "number" : ["string", "number"],
    enum: actualValues
  };
}
function parseNeverDef(refs) {
  return refs.target === "openAi" ? void 0 : {
    not: parseAnyDef({
      ...refs,
      currentPath: [...refs.currentPath, "not"]
    })
  };
}
function parseNullDef(refs) {
  return refs.target === "openApi3" ? {
    enum: ["null"],
    nullable: true
  } : {
    type: "null"
  };
}
const primitiveMappings = {
  ZodString: "string",
  ZodNumber: "number",
  ZodBigInt: "integer",
  ZodBoolean: "boolean",
  ZodNull: "null"
};
function parseUnionDef(def, refs) {
  if (refs.target === "openApi3")
    return asAnyOf(def, refs);
  const options = def.options instanceof Map ? Array.from(def.options.values()) : def.options;
  if (options.every((x2) => x2._def.typeName in primitiveMappings && (!x2._def.checks || !x2._def.checks.length))) {
    const types = options.reduce((types2, x2) => {
      const type2 = primitiveMappings[x2._def.typeName];
      return type2 && !types2.includes(type2) ? [...types2, type2] : types2;
    }, []);
    return {
      type: types.length > 1 ? types : types[0]
    };
  } else if (options.every((x2) => x2._def.typeName === "ZodLiteral" && !x2.description)) {
    const types = options.reduce((acc, x2) => {
      const type2 = typeof x2._def.value;
      switch (type2) {
        case "string":
        case "number":
        case "boolean":
          return [...acc, type2];
        case "bigint":
          return [...acc, "integer"];
        case "object":
          if (x2._def.value === null)
            return [...acc, "null"];
        case "symbol":
        case "undefined":
        case "function":
        default:
          return acc;
      }
    }, []);
    if (types.length === options.length) {
      const uniqueTypes = types.filter((x2, i2, a2) => a2.indexOf(x2) === i2);
      return {
        type: uniqueTypes.length > 1 ? uniqueTypes : uniqueTypes[0],
        enum: options.reduce((acc, x2) => {
          return acc.includes(x2._def.value) ? acc : [...acc, x2._def.value];
        }, [])
      };
    }
  } else if (options.every((x2) => x2._def.typeName === "ZodEnum")) {
    return {
      type: "string",
      enum: options.reduce((acc, x2) => [
        ...acc,
        ...x2._def.values.filter((x3) => !acc.includes(x3))
      ], [])
    };
  }
  return asAnyOf(def, refs);
}
const asAnyOf = (def, refs) => {
  const anyOf2 = (def.options instanceof Map ? Array.from(def.options.values()) : def.options).map((x2, i2) => parseDef(x2._def, {
    ...refs,
    currentPath: [...refs.currentPath, "anyOf", `${i2}`]
  })).filter((x2) => !!x2 && (!refs.strictUnions || typeof x2 === "object" && Object.keys(x2).length > 0));
  return anyOf2.length ? { anyOf: anyOf2 } : void 0;
};
function parseNullableDef(def, refs) {
  if (["ZodString", "ZodNumber", "ZodBigInt", "ZodBoolean", "ZodNull"].includes(def.innerType._def.typeName) && (!def.innerType._def.checks || !def.innerType._def.checks.length)) {
    if (refs.target === "openApi3") {
      return {
        type: primitiveMappings[def.innerType._def.typeName],
        nullable: true
      };
    }
    return {
      type: [
        primitiveMappings[def.innerType._def.typeName],
        "null"
      ]
    };
  }
  if (refs.target === "openApi3") {
    const base2 = parseDef(def.innerType._def, {
      ...refs,
      currentPath: [...refs.currentPath]
    });
    if (base2 && "$ref" in base2)
      return { allOf: [base2], nullable: true };
    return base2 && { ...base2, nullable: true };
  }
  const base = parseDef(def.innerType._def, {
    ...refs,
    currentPath: [...refs.currentPath, "anyOf", "0"]
  });
  return base && { anyOf: [base, { type: "null" }] };
}
function parseNumberDef(def, refs) {
  const res = {
    type: "number"
  };
  if (!def.checks)
    return res;
  for (const check of def.checks) {
    switch (check.kind) {
      case "int":
        res.type = "integer";
        addErrorMessage(res, "type", check.message, refs);
        break;
      case "min":
        if (refs.target === "jsonSchema7") {
          if (check.inclusive) {
            setResponseValueAndErrors(res, "minimum", check.value, check.message, refs);
          } else {
            setResponseValueAndErrors(res, "exclusiveMinimum", check.value, check.message, refs);
          }
        } else {
          if (!check.inclusive) {
            res.exclusiveMinimum = true;
          }
          setResponseValueAndErrors(res, "minimum", check.value, check.message, refs);
        }
        break;
      case "max":
        if (refs.target === "jsonSchema7") {
          if (check.inclusive) {
            setResponseValueAndErrors(res, "maximum", check.value, check.message, refs);
          } else {
            setResponseValueAndErrors(res, "exclusiveMaximum", check.value, check.message, refs);
          }
        } else {
          if (!check.inclusive) {
            res.exclusiveMaximum = true;
          }
          setResponseValueAndErrors(res, "maximum", check.value, check.message, refs);
        }
        break;
      case "multipleOf":
        setResponseValueAndErrors(res, "multipleOf", check.value, check.message, refs);
        break;
    }
  }
  return res;
}
function parseObjectDef(def, refs) {
  const forceOptionalIntoNullable = refs.target === "openAi";
  const result = {
    type: "object",
    properties: {}
  };
  const required2 = [];
  const shape = def.shape();
  for (const propName in shape) {
    let propDef = shape[propName];
    if (propDef === void 0 || propDef._def === void 0) {
      continue;
    }
    let propOptional = safeIsOptional(propDef);
    if (propOptional && forceOptionalIntoNullable) {
      if (propDef._def.typeName === "ZodOptional") {
        propDef = propDef._def.innerType;
      }
      if (!propDef.isNullable()) {
        propDef = propDef.nullable();
      }
      propOptional = false;
    }
    const parsedDef = parseDef(propDef._def, {
      ...refs,
      currentPath: [...refs.currentPath, "properties", propName],
      propertyPath: [...refs.currentPath, "properties", propName]
    });
    if (parsedDef === void 0) {
      continue;
    }
    result.properties[propName] = parsedDef;
    if (!propOptional) {
      required2.push(propName);
    }
  }
  if (required2.length) {
    result.required = required2;
  }
  const additionalProperties2 = decideAdditionalProperties(def, refs);
  if (additionalProperties2 !== void 0) {
    result.additionalProperties = additionalProperties2;
  }
  return result;
}
function decideAdditionalProperties(def, refs) {
  if (def.catchall._def.typeName !== "ZodNever") {
    return parseDef(def.catchall._def, {
      ...refs,
      currentPath: [...refs.currentPath, "additionalProperties"]
    });
  }
  switch (def.unknownKeys) {
    case "passthrough":
      return refs.allowedAdditionalProperties;
    case "strict":
      return refs.rejectedAdditionalProperties;
    case "strip":
      return refs.removeAdditionalStrategy === "strict" ? refs.allowedAdditionalProperties : refs.rejectedAdditionalProperties;
  }
}
function safeIsOptional(schema) {
  try {
    return schema.isOptional();
  } catch {
    return true;
  }
}
const parseOptionalDef = (def, refs) => {
  var _a;
  if (refs.currentPath.toString() === ((_a = refs.propertyPath) == null ? void 0 : _a.toString())) {
    return parseDef(def.innerType._def, refs);
  }
  const innerSchema = parseDef(def.innerType._def, {
    ...refs,
    currentPath: [...refs.currentPath, "anyOf", "1"]
  });
  return innerSchema ? {
    anyOf: [
      {
        not: parseAnyDef(refs)
      },
      innerSchema
    ]
  } : parseAnyDef(refs);
};
const parsePipelineDef = (def, refs) => {
  if (refs.pipeStrategy === "input") {
    return parseDef(def.in._def, refs);
  } else if (refs.pipeStrategy === "output") {
    return parseDef(def.out._def, refs);
  }
  const a2 = parseDef(def.in._def, {
    ...refs,
    currentPath: [...refs.currentPath, "allOf", "0"]
  });
  const b2 = parseDef(def.out._def, {
    ...refs,
    currentPath: [...refs.currentPath, "allOf", a2 ? "1" : "0"]
  });
  return {
    allOf: [a2, b2].filter((x2) => x2 !== void 0)
  };
};
function parsePromiseDef(def, refs) {
  return parseDef(def.type._def, refs);
}
function parseSetDef(def, refs) {
  const items2 = parseDef(def.valueType._def, {
    ...refs,
    currentPath: [...refs.currentPath, "items"]
  });
  const schema = {
    type: "array",
    uniqueItems: true,
    items: items2
  };
  if (def.minSize) {
    setResponseValueAndErrors(schema, "minItems", def.minSize.value, def.minSize.message, refs);
  }
  if (def.maxSize) {
    setResponseValueAndErrors(schema, "maxItems", def.maxSize.value, def.maxSize.message, refs);
  }
  return schema;
}
function parseTupleDef(def, refs) {
  if (def.rest) {
    return {
      type: "array",
      minItems: def.items.length,
      items: def.items.map((x2, i2) => parseDef(x2._def, {
        ...refs,
        currentPath: [...refs.currentPath, "items", `${i2}`]
      })).reduce((acc, x2) => x2 === void 0 ? acc : [...acc, x2], []),
      additionalItems: parseDef(def.rest._def, {
        ...refs,
        currentPath: [...refs.currentPath, "additionalItems"]
      })
    };
  } else {
    return {
      type: "array",
      minItems: def.items.length,
      maxItems: def.items.length,
      items: def.items.map((x2, i2) => parseDef(x2._def, {
        ...refs,
        currentPath: [...refs.currentPath, "items", `${i2}`]
      })).reduce((acc, x2) => x2 === void 0 ? acc : [...acc, x2], [])
    };
  }
}
function parseUndefinedDef(refs) {
  return {
    not: parseAnyDef(refs)
  };
}
function parseUnknownDef(refs) {
  return parseAnyDef(refs);
}
const parseReadonlyDef = (def, refs) => {
  return parseDef(def.innerType._def, refs);
};
const selectParser = (def, typeName, refs) => {
  switch (typeName) {
    case ZodFirstPartyTypeKind.ZodString:
      return parseStringDef(def, refs);
    case ZodFirstPartyTypeKind.ZodNumber:
      return parseNumberDef(def, refs);
    case ZodFirstPartyTypeKind.ZodObject:
      return parseObjectDef(def, refs);
    case ZodFirstPartyTypeKind.ZodBigInt:
      return parseBigintDef(def, refs);
    case ZodFirstPartyTypeKind.ZodBoolean:
      return parseBooleanDef();
    case ZodFirstPartyTypeKind.ZodDate:
      return parseDateDef(def, refs);
    case ZodFirstPartyTypeKind.ZodUndefined:
      return parseUndefinedDef(refs);
    case ZodFirstPartyTypeKind.ZodNull:
      return parseNullDef(refs);
    case ZodFirstPartyTypeKind.ZodArray:
      return parseArrayDef(def, refs);
    case ZodFirstPartyTypeKind.ZodUnion:
    case ZodFirstPartyTypeKind.ZodDiscriminatedUnion:
      return parseUnionDef(def, refs);
    case ZodFirstPartyTypeKind.ZodIntersection:
      return parseIntersectionDef(def, refs);
    case ZodFirstPartyTypeKind.ZodTuple:
      return parseTupleDef(def, refs);
    case ZodFirstPartyTypeKind.ZodRecord:
      return parseRecordDef(def, refs);
    case ZodFirstPartyTypeKind.ZodLiteral:
      return parseLiteralDef(def, refs);
    case ZodFirstPartyTypeKind.ZodEnum:
      return parseEnumDef(def);
    case ZodFirstPartyTypeKind.ZodNativeEnum:
      return parseNativeEnumDef(def);
    case ZodFirstPartyTypeKind.ZodNullable:
      return parseNullableDef(def, refs);
    case ZodFirstPartyTypeKind.ZodOptional:
      return parseOptionalDef(def, refs);
    case ZodFirstPartyTypeKind.ZodMap:
      return parseMapDef(def, refs);
    case ZodFirstPartyTypeKind.ZodSet:
      return parseSetDef(def, refs);
    case ZodFirstPartyTypeKind.ZodLazy:
      return () => def.getter()._def;
    case ZodFirstPartyTypeKind.ZodPromise:
      return parsePromiseDef(def, refs);
    case ZodFirstPartyTypeKind.ZodNaN:
    case ZodFirstPartyTypeKind.ZodNever:
      return parseNeverDef(refs);
    case ZodFirstPartyTypeKind.ZodEffects:
      return parseEffectsDef(def, refs);
    case ZodFirstPartyTypeKind.ZodAny:
      return parseAnyDef(refs);
    case ZodFirstPartyTypeKind.ZodUnknown:
      return parseUnknownDef(refs);
    case ZodFirstPartyTypeKind.ZodDefault:
      return parseDefaultDef(def, refs);
    case ZodFirstPartyTypeKind.ZodBranded:
      return parseBrandedDef(def, refs);
    case ZodFirstPartyTypeKind.ZodReadonly:
      return parseReadonlyDef(def, refs);
    case ZodFirstPartyTypeKind.ZodCatch:
      return parseCatchDef(def, refs);
    case ZodFirstPartyTypeKind.ZodPipeline:
      return parsePipelineDef(def, refs);
    case ZodFirstPartyTypeKind.ZodFunction:
    case ZodFirstPartyTypeKind.ZodVoid:
    case ZodFirstPartyTypeKind.ZodSymbol:
      return void 0;
    default:
      return /* @__PURE__ */ ((_2) => void 0)();
  }
};
function parseDef(def, refs, forceResolution = false) {
  var _a;
  const seenItem = refs.seen.get(def);
  if (refs.override) {
    const overrideResult = (_a = refs.override) == null ? void 0 : _a.call(refs, def, refs, seenItem, forceResolution);
    if (overrideResult !== ignoreOverride) {
      return overrideResult;
    }
  }
  if (seenItem && !forceResolution) {
    const seenSchema = get$ref(seenItem, refs);
    if (seenSchema !== void 0) {
      return seenSchema;
    }
  }
  const newItem = { def, path: refs.currentPath, jsonSchema: void 0 };
  refs.seen.set(def, newItem);
  const jsonSchemaOrGetter = selectParser(def, def.typeName, refs);
  const jsonSchema = typeof jsonSchemaOrGetter === "function" ? parseDef(jsonSchemaOrGetter(), refs) : jsonSchemaOrGetter;
  if (jsonSchema) {
    addMeta(def, refs, jsonSchema);
  }
  if (refs.postProcess) {
    const postProcessResult = refs.postProcess(jsonSchema, def, refs);
    newItem.jsonSchema = jsonSchema;
    return postProcessResult;
  }
  newItem.jsonSchema = jsonSchema;
  return jsonSchema;
}
const get$ref = (item, refs) => {
  switch (refs.$refStrategy) {
    case "root":
      return { $ref: item.path.join("/") };
    case "relative":
      return { $ref: getRelativePath(refs.currentPath, item.path) };
    case "none":
    case "seen": {
      if (item.path.length < refs.currentPath.length && item.path.every((value, index) => refs.currentPath[index] === value)) {
        console.warn(`Recursive reference detected at ${refs.currentPath.join("/")}! Defaulting to any`);
        return parseAnyDef(refs);
      }
      return refs.$refStrategy === "seen" ? parseAnyDef(refs) : void 0;
    }
  }
};
const addMeta = (def, refs, jsonSchema) => {
  if (def.description) {
    jsonSchema.description = def.description;
    if (refs.markdownDescription) {
      jsonSchema.markdownDescription = def.description;
    }
  }
  return jsonSchema;
};
const zodToJsonSchema = (schema, options) => {
  const refs = getRefs(options);
  let definitions2 = typeof options === "object" && options.definitions ? Object.entries(options.definitions).reduce((acc, [name2, schema2]) => ({
    ...acc,
    [name2]: parseDef(schema2._def, {
      ...refs,
      currentPath: [...refs.basePath, refs.definitionPath, name2]
    }, true) ?? parseAnyDef(refs)
  }), {}) : void 0;
  const name = typeof options === "string" ? options : (options == null ? void 0 : options.nameStrategy) === "title" ? void 0 : options == null ? void 0 : options.name;
  const main = parseDef(schema._def, name === void 0 ? refs : {
    ...refs,
    currentPath: [...refs.basePath, refs.definitionPath, name]
  }, false) ?? parseAnyDef(refs);
  const title2 = typeof options === "object" && options.name !== void 0 && options.nameStrategy === "title" ? options.name : void 0;
  if (title2 !== void 0) {
    main.title = title2;
  }
  if (refs.flags.hasReferencedOpenAiAnyType) {
    if (!definitions2) {
      definitions2 = {};
    }
    if (!definitions2[refs.openAiAnyTypeName]) {
      definitions2[refs.openAiAnyTypeName] = {
        // Skipping "object" as no properties can be defined and additionalProperties must be "false"
        type: ["string", "number", "integer", "boolean", "array", "null"],
        items: {
          $ref: refs.$refStrategy === "relative" ? "1" : [
            ...refs.basePath,
            refs.definitionPath,
            refs.openAiAnyTypeName
          ].join("/")
        }
      };
    }
  }
  const combined = name === void 0 ? definitions2 ? {
    ...main,
    [refs.definitionPath]: definitions2
  } : main : {
    $ref: [
      ...refs.$refStrategy === "relative" ? [] : refs.basePath,
      refs.definitionPath,
      name
    ].join("/"),
    [refs.definitionPath]: {
      ...definitions2,
      [name]: main
    }
  };
  if (refs.target === "jsonSchema7") {
    combined.$schema = "http://json-schema.org/draft-07/schema#";
  } else if (refs.target === "jsonSchema2019-09" || refs.target === "openAi") {
    combined.$schema = "https://json-schema.org/draft/2019-09/schema#";
  }
  if (refs.target === "openAi" && ("anyOf" in combined || "oneOf" in combined || "allOf" in combined || "type" in combined && Array.isArray(combined.type))) {
    console.warn("Warning: OpenAI may not support schemas with unions as roots! Try wrapping it in an object property.");
  }
  return combined;
};
var McpZodTypeKind;
(function(McpZodTypeKind2) {
  McpZodTypeKind2["Completable"] = "McpCompletable";
})(McpZodTypeKind || (McpZodTypeKind = {}));
class Completable extends ZodType {
  _parse(input) {
    const { ctx } = this._processInputParams(input);
    const data2 = ctx.data;
    return this._def.type._parse({
      data: data2,
      path: ctx.path,
      parent: ctx
    });
  }
  unwrap() {
    return this._def.type;
  }
}
Completable.create = (type2, params) => {
  return new Completable({
    type: type2,
    typeName: McpZodTypeKind.Completable,
    complete: params.complete,
    ...processCreateParams(params)
  });
};
function processCreateParams(params) {
  if (!params)
    return {};
  const { errorMap: errorMap2, invalid_type_error, required_error, description: description2 } = params;
  if (errorMap2 && (invalid_type_error || required_error)) {
    throw new Error(`Can't use "invalid_type_error" or "required_error" in conjunction with custom error map.`);
  }
  if (errorMap2)
    return { errorMap: errorMap2, description: description2 };
  const customMap = (iss, ctx) => {
    var _a, _b;
    const { message } = params;
    if (iss.code === "invalid_enum_value") {
      return { message: message !== null && message !== void 0 ? message : ctx.defaultError };
    }
    if (typeof ctx.data === "undefined") {
      return { message: (_a = message !== null && message !== void 0 ? message : required_error) !== null && _a !== void 0 ? _a : ctx.defaultError };
    }
    if (iss.code !== "invalid_type")
      return { message: ctx.defaultError };
    return { message: (_b = message !== null && message !== void 0 ? message : invalid_type_error) !== null && _b !== void 0 ? _b : ctx.defaultError };
  };
  return { errorMap: customMap, description: description2 };
}
class McpServer {
  constructor(serverInfo, options) {
    this._registeredResources = {};
    this._registeredResourceTemplates = {};
    this._registeredTools = {};
    this._registeredPrompts = {};
    this._toolHandlersInitialized = false;
    this._completionHandlerInitialized = false;
    this._resourceHandlersInitialized = false;
    this._promptHandlersInitialized = false;
    this.server = new Server(serverInfo, options);
  }
  /**
   * Attaches to the given transport, starts it, and starts listening for messages.
   *
   * The `server` object assumes ownership of the Transport, replacing any callbacks that have already been set, and expects that it is the only user of the Transport instance going forward.
   */
  async connect(transport) {
    return await this.server.connect(transport);
  }
  /**
   * Closes the connection.
   */
  async close() {
    await this.server.close();
  }
  setToolRequestHandlers() {
    if (this._toolHandlersInitialized) {
      return;
    }
    this.server.assertCanSetRequestHandler(ListToolsRequestSchema.shape.method.value);
    this.server.assertCanSetRequestHandler(CallToolRequestSchema.shape.method.value);
    this.server.registerCapabilities({
      tools: {
        listChanged: true
      }
    });
    this.server.setRequestHandler(ListToolsRequestSchema, () => ({
      tools: Object.entries(this._registeredTools).filter(([, tool]) => tool.enabled).map(([name, tool]) => {
        const toolDefinition = {
          name,
          title: tool.title,
          description: tool.description,
          inputSchema: tool.inputSchema ? zodToJsonSchema(tool.inputSchema, {
            strictUnions: true
          }) : EMPTY_OBJECT_JSON_SCHEMA,
          annotations: tool.annotations,
          _meta: tool._meta
        };
        if (tool.outputSchema) {
          toolDefinition.outputSchema = zodToJsonSchema(tool.outputSchema, {
            strictUnions: true
          });
        }
        return toolDefinition;
      })
    }));
    this.server.setRequestHandler(CallToolRequestSchema, async (request, extra) => {
      const tool = this._registeredTools[request.params.name];
      if (!tool) {
        throw new McpError(ErrorCode.InvalidParams, `Tool ${request.params.name} not found`);
      }
      if (!tool.enabled) {
        throw new McpError(ErrorCode.InvalidParams, `Tool ${request.params.name} disabled`);
      }
      let result;
      if (tool.inputSchema) {
        const parseResult = await tool.inputSchema.safeParseAsync(request.params.arguments);
        if (!parseResult.success) {
          throw new McpError(ErrorCode.InvalidParams, `Invalid arguments for tool ${request.params.name}: ${parseResult.error.message}`);
        }
        const args = parseResult.data;
        const cb = tool.callback;
        try {
          result = await Promise.resolve(cb(args, extra));
        } catch (error) {
          result = {
            content: [
              {
                type: "text",
                text: error instanceof Error ? error.message : String(error)
              }
            ],
            isError: true
          };
        }
      } else {
        const cb = tool.callback;
        try {
          result = await Promise.resolve(cb(extra));
        } catch (error) {
          result = {
            content: [
              {
                type: "text",
                text: error instanceof Error ? error.message : String(error)
              }
            ],
            isError: true
          };
        }
      }
      if (tool.outputSchema && !result.isError) {
        if (!result.structuredContent) {
          throw new McpError(ErrorCode.InvalidParams, `Tool ${request.params.name} has an output schema but no structured content was provided`);
        }
        const parseResult = await tool.outputSchema.safeParseAsync(result.structuredContent);
        if (!parseResult.success) {
          throw new McpError(ErrorCode.InvalidParams, `Invalid structured content for tool ${request.params.name}: ${parseResult.error.message}`);
        }
      }
      return result;
    });
    this._toolHandlersInitialized = true;
  }
  setCompletionRequestHandler() {
    if (this._completionHandlerInitialized) {
      return;
    }
    this.server.assertCanSetRequestHandler(CompleteRequestSchema.shape.method.value);
    this.server.registerCapabilities({
      completions: {}
    });
    this.server.setRequestHandler(CompleteRequestSchema, async (request) => {
      switch (request.params.ref.type) {
        case "ref/prompt":
          return this.handlePromptCompletion(request, request.params.ref);
        case "ref/resource":
          return this.handleResourceCompletion(request, request.params.ref);
        default:
          throw new McpError(ErrorCode.InvalidParams, `Invalid completion reference: ${request.params.ref}`);
      }
    });
    this._completionHandlerInitialized = true;
  }
  async handlePromptCompletion(request, ref2) {
    const prompt = this._registeredPrompts[ref2.name];
    if (!prompt) {
      throw new McpError(ErrorCode.InvalidParams, `Prompt ${ref2.name} not found`);
    }
    if (!prompt.enabled) {
      throw new McpError(ErrorCode.InvalidParams, `Prompt ${ref2.name} disabled`);
    }
    if (!prompt.argsSchema) {
      return EMPTY_COMPLETION_RESULT;
    }
    const field = prompt.argsSchema.shape[request.params.argument.name];
    if (!(field instanceof Completable)) {
      return EMPTY_COMPLETION_RESULT;
    }
    const def = field._def;
    const suggestions = await def.complete(request.params.argument.value, request.params.context);
    return createCompletionResult(suggestions);
  }
  async handleResourceCompletion(request, ref2) {
    const template = Object.values(this._registeredResourceTemplates).find((t2) => t2.resourceTemplate.uriTemplate.toString() === ref2.uri);
    if (!template) {
      if (this._registeredResources[ref2.uri]) {
        return EMPTY_COMPLETION_RESULT;
      }
      throw new McpError(ErrorCode.InvalidParams, `Resource template ${request.params.ref.uri} not found`);
    }
    const completer = template.resourceTemplate.completeCallback(request.params.argument.name);
    if (!completer) {
      return EMPTY_COMPLETION_RESULT;
    }
    const suggestions = await completer(request.params.argument.value, request.params.context);
    return createCompletionResult(suggestions);
  }
  setResourceRequestHandlers() {
    if (this._resourceHandlersInitialized) {
      return;
    }
    this.server.assertCanSetRequestHandler(ListResourcesRequestSchema.shape.method.value);
    this.server.assertCanSetRequestHandler(ListResourceTemplatesRequestSchema.shape.method.value);
    this.server.assertCanSetRequestHandler(ReadResourceRequestSchema.shape.method.value);
    this.server.registerCapabilities({
      resources: {
        listChanged: true
      }
    });
    this.server.setRequestHandler(ListResourcesRequestSchema, async (request, extra) => {
      const resources = Object.entries(this._registeredResources).filter(([_2, resource]) => resource.enabled).map(([uri, resource]) => ({
        uri,
        name: resource.name,
        ...resource.metadata
      }));
      const templateResources = [];
      for (const template of Object.values(this._registeredResourceTemplates)) {
        if (!template.resourceTemplate.listCallback) {
          continue;
        }
        const result = await template.resourceTemplate.listCallback(extra);
        for (const resource of result.resources) {
          templateResources.push({
            ...template.metadata,
            // the defined resource metadata should override the template metadata if present
            ...resource
          });
        }
      }
      return { resources: [...resources, ...templateResources] };
    });
    this.server.setRequestHandler(ListResourceTemplatesRequestSchema, async () => {
      const resourceTemplates = Object.entries(this._registeredResourceTemplates).map(([name, template]) => ({
        name,
        uriTemplate: template.resourceTemplate.uriTemplate.toString(),
        ...template.metadata
      }));
      return { resourceTemplates };
    });
    this.server.setRequestHandler(ReadResourceRequestSchema, async (request, extra) => {
      const uri = new URL(request.params.uri);
      const resource = this._registeredResources[uri.toString()];
      if (resource) {
        if (!resource.enabled) {
          throw new McpError(ErrorCode.InvalidParams, `Resource ${uri} disabled`);
        }
        return resource.readCallback(uri, extra);
      }
      for (const template of Object.values(this._registeredResourceTemplates)) {
        const variables = template.resourceTemplate.uriTemplate.match(uri.toString());
        if (variables) {
          return template.readCallback(uri, variables, extra);
        }
      }
      throw new McpError(ErrorCode.InvalidParams, `Resource ${uri} not found`);
    });
    this.setCompletionRequestHandler();
    this._resourceHandlersInitialized = true;
  }
  setPromptRequestHandlers() {
    if (this._promptHandlersInitialized) {
      return;
    }
    this.server.assertCanSetRequestHandler(ListPromptsRequestSchema.shape.method.value);
    this.server.assertCanSetRequestHandler(GetPromptRequestSchema.shape.method.value);
    this.server.registerCapabilities({
      prompts: {
        listChanged: true
      }
    });
    this.server.setRequestHandler(ListPromptsRequestSchema, () => ({
      prompts: Object.entries(this._registeredPrompts).filter(([, prompt]) => prompt.enabled).map(([name, prompt]) => {
        return {
          name,
          title: prompt.title,
          description: prompt.description,
          arguments: prompt.argsSchema ? promptArgumentsFromSchema(prompt.argsSchema) : void 0
        };
      })
    }));
    this.server.setRequestHandler(GetPromptRequestSchema, async (request, extra) => {
      const prompt = this._registeredPrompts[request.params.name];
      if (!prompt) {
        throw new McpError(ErrorCode.InvalidParams, `Prompt ${request.params.name} not found`);
      }
      if (!prompt.enabled) {
        throw new McpError(ErrorCode.InvalidParams, `Prompt ${request.params.name} disabled`);
      }
      if (prompt.argsSchema) {
        const parseResult = await prompt.argsSchema.safeParseAsync(request.params.arguments);
        if (!parseResult.success) {
          throw new McpError(ErrorCode.InvalidParams, `Invalid arguments for prompt ${request.params.name}: ${parseResult.error.message}`);
        }
        const args = parseResult.data;
        const cb = prompt.callback;
        return await Promise.resolve(cb(args, extra));
      } else {
        const cb = prompt.callback;
        return await Promise.resolve(cb(extra));
      }
    });
    this.setCompletionRequestHandler();
    this._promptHandlersInitialized = true;
  }
  resource(name, uriOrTemplate, ...rest) {
    let metadata;
    if (typeof rest[0] === "object") {
      metadata = rest.shift();
    }
    const readCallback = rest[0];
    if (typeof uriOrTemplate === "string") {
      if (this._registeredResources[uriOrTemplate]) {
        throw new Error(`Resource ${uriOrTemplate} is already registered`);
      }
      const registeredResource = this._createRegisteredResource(name, void 0, uriOrTemplate, metadata, readCallback);
      this.setResourceRequestHandlers();
      this.sendResourceListChanged();
      return registeredResource;
    } else {
      if (this._registeredResourceTemplates[name]) {
        throw new Error(`Resource template ${name} is already registered`);
      }
      const registeredResourceTemplate = this._createRegisteredResourceTemplate(name, void 0, uriOrTemplate, metadata, readCallback);
      this.setResourceRequestHandlers();
      this.sendResourceListChanged();
      return registeredResourceTemplate;
    }
  }
  registerResource(name, uriOrTemplate, config, readCallback) {
    if (typeof uriOrTemplate === "string") {
      if (this._registeredResources[uriOrTemplate]) {
        throw new Error(`Resource ${uriOrTemplate} is already registered`);
      }
      const registeredResource = this._createRegisteredResource(name, config.title, uriOrTemplate, config, readCallback);
      this.setResourceRequestHandlers();
      this.sendResourceListChanged();
      return registeredResource;
    } else {
      if (this._registeredResourceTemplates[name]) {
        throw new Error(`Resource template ${name} is already registered`);
      }
      const registeredResourceTemplate = this._createRegisteredResourceTemplate(name, config.title, uriOrTemplate, config, readCallback);
      this.setResourceRequestHandlers();
      this.sendResourceListChanged();
      return registeredResourceTemplate;
    }
  }
  _createRegisteredResource(name, title2, uri, metadata, readCallback) {
    const registeredResource = {
      name,
      title: title2,
      metadata,
      readCallback,
      enabled: true,
      disable: () => registeredResource.update({ enabled: false }),
      enable: () => registeredResource.update({ enabled: true }),
      remove: () => registeredResource.update({ uri: null }),
      update: (updates) => {
        if (typeof updates.uri !== "undefined" && updates.uri !== uri) {
          delete this._registeredResources[uri];
          if (updates.uri)
            this._registeredResources[updates.uri] = registeredResource;
        }
        if (typeof updates.name !== "undefined")
          registeredResource.name = updates.name;
        if (typeof updates.title !== "undefined")
          registeredResource.title = updates.title;
        if (typeof updates.metadata !== "undefined")
          registeredResource.metadata = updates.metadata;
        if (typeof updates.callback !== "undefined")
          registeredResource.readCallback = updates.callback;
        if (typeof updates.enabled !== "undefined")
          registeredResource.enabled = updates.enabled;
        this.sendResourceListChanged();
      }
    };
    this._registeredResources[uri] = registeredResource;
    return registeredResource;
  }
  _createRegisteredResourceTemplate(name, title2, template, metadata, readCallback) {
    const registeredResourceTemplate = {
      resourceTemplate: template,
      title: title2,
      metadata,
      readCallback,
      enabled: true,
      disable: () => registeredResourceTemplate.update({ enabled: false }),
      enable: () => registeredResourceTemplate.update({ enabled: true }),
      remove: () => registeredResourceTemplate.update({ name: null }),
      update: (updates) => {
        if (typeof updates.name !== "undefined" && updates.name !== name) {
          delete this._registeredResourceTemplates[name];
          if (updates.name)
            this._registeredResourceTemplates[updates.name] = registeredResourceTemplate;
        }
        if (typeof updates.title !== "undefined")
          registeredResourceTemplate.title = updates.title;
        if (typeof updates.template !== "undefined")
          registeredResourceTemplate.resourceTemplate = updates.template;
        if (typeof updates.metadata !== "undefined")
          registeredResourceTemplate.metadata = updates.metadata;
        if (typeof updates.callback !== "undefined")
          registeredResourceTemplate.readCallback = updates.callback;
        if (typeof updates.enabled !== "undefined")
          registeredResourceTemplate.enabled = updates.enabled;
        this.sendResourceListChanged();
      }
    };
    this._registeredResourceTemplates[name] = registeredResourceTemplate;
    return registeredResourceTemplate;
  }
  _createRegisteredPrompt(name, title2, description2, argsSchema, callback) {
    const registeredPrompt = {
      title: title2,
      description: description2,
      argsSchema: argsSchema === void 0 ? void 0 : objectType(argsSchema),
      callback,
      enabled: true,
      disable: () => registeredPrompt.update({ enabled: false }),
      enable: () => registeredPrompt.update({ enabled: true }),
      remove: () => registeredPrompt.update({ name: null }),
      update: (updates) => {
        if (typeof updates.name !== "undefined" && updates.name !== name) {
          delete this._registeredPrompts[name];
          if (updates.name)
            this._registeredPrompts[updates.name] = registeredPrompt;
        }
        if (typeof updates.title !== "undefined")
          registeredPrompt.title = updates.title;
        if (typeof updates.description !== "undefined")
          registeredPrompt.description = updates.description;
        if (typeof updates.argsSchema !== "undefined")
          registeredPrompt.argsSchema = objectType(updates.argsSchema);
        if (typeof updates.callback !== "undefined")
          registeredPrompt.callback = updates.callback;
        if (typeof updates.enabled !== "undefined")
          registeredPrompt.enabled = updates.enabled;
        this.sendPromptListChanged();
      }
    };
    this._registeredPrompts[name] = registeredPrompt;
    return registeredPrompt;
  }
  _createRegisteredTool(name, title2, description2, inputSchema, outputSchema, annotations, _meta, callback) {
    const registeredTool = {
      title: title2,
      description: description2,
      inputSchema: inputSchema === void 0 ? void 0 : objectType(inputSchema),
      outputSchema: outputSchema === void 0 ? void 0 : objectType(outputSchema),
      annotations,
      _meta,
      callback,
      enabled: true,
      disable: () => registeredTool.update({ enabled: false }),
      enable: () => registeredTool.update({ enabled: true }),
      remove: () => registeredTool.update({ name: null }),
      update: (updates) => {
        if (typeof updates.name !== "undefined" && updates.name !== name) {
          delete this._registeredTools[name];
          if (updates.name)
            this._registeredTools[updates.name] = registeredTool;
        }
        if (typeof updates.title !== "undefined")
          registeredTool.title = updates.title;
        if (typeof updates.description !== "undefined")
          registeredTool.description = updates.description;
        if (typeof updates.paramsSchema !== "undefined")
          registeredTool.inputSchema = objectType(updates.paramsSchema);
        if (typeof updates.callback !== "undefined")
          registeredTool.callback = updates.callback;
        if (typeof updates.annotations !== "undefined")
          registeredTool.annotations = updates.annotations;
        if (typeof updates._meta !== "undefined")
          registeredTool._meta = updates._meta;
        if (typeof updates.enabled !== "undefined")
          registeredTool.enabled = updates.enabled;
        this.sendToolListChanged();
      }
    };
    this._registeredTools[name] = registeredTool;
    this.setToolRequestHandlers();
    this.sendToolListChanged();
    return registeredTool;
  }
  /**
   * tool() implementation. Parses arguments passed to overrides defined above.
   */
  tool(name, ...rest) {
    if (this._registeredTools[name]) {
      throw new Error(`Tool ${name} is already registered`);
    }
    let description2;
    let inputSchema;
    let outputSchema;
    let annotations;
    if (typeof rest[0] === "string") {
      description2 = rest.shift();
    }
    if (rest.length > 1) {
      const firstArg = rest[0];
      if (isZodRawShape(firstArg)) {
        inputSchema = rest.shift();
        if (rest.length > 1 && typeof rest[0] === "object" && rest[0] !== null && !isZodRawShape(rest[0])) {
          annotations = rest.shift();
        }
      } else if (typeof firstArg === "object" && firstArg !== null) {
        annotations = rest.shift();
      }
    }
    const callback = rest[0];
    return this._createRegisteredTool(name, void 0, description2, inputSchema, outputSchema, annotations, void 0, callback);
  }
  /**
   * Registers a tool with a config object and callback.
   */
  registerTool(name, config, cb) {
    if (this._registeredTools[name]) {
      throw new Error(`Tool ${name} is already registered`);
    }
    const { title: title2, description: description2, inputSchema, outputSchema, annotations, _meta } = config;
    return this._createRegisteredTool(name, title2, description2, inputSchema, outputSchema, annotations, _meta, cb);
  }
  prompt(name, ...rest) {
    if (this._registeredPrompts[name]) {
      throw new Error(`Prompt ${name} is already registered`);
    }
    let description2;
    if (typeof rest[0] === "string") {
      description2 = rest.shift();
    }
    let argsSchema;
    if (rest.length > 1) {
      argsSchema = rest.shift();
    }
    const cb = rest[0];
    const registeredPrompt = this._createRegisteredPrompt(name, void 0, description2, argsSchema, cb);
    this.setPromptRequestHandlers();
    this.sendPromptListChanged();
    return registeredPrompt;
  }
  /**
   * Registers a prompt with a config object and callback.
   */
  registerPrompt(name, config, cb) {
    if (this._registeredPrompts[name]) {
      throw new Error(`Prompt ${name} is already registered`);
    }
    const { title: title2, description: description2, argsSchema } = config;
    const registeredPrompt = this._createRegisteredPrompt(name, title2, description2, argsSchema, cb);
    this.setPromptRequestHandlers();
    this.sendPromptListChanged();
    return registeredPrompt;
  }
  /**
   * Checks if the server is connected to a transport.
   * @returns True if the server is connected
   */
  isConnected() {
    return this.server.transport !== void 0;
  }
  /**
   * Sends a logging message to the client, if connected.
   * Note: You only need to send the parameters object, not the entire JSON RPC message
   * @see LoggingMessageNotification
   * @param params
   * @param sessionId optional for stateless and backward compatibility
   */
  async sendLoggingMessage(params, sessionId) {
    return this.server.sendLoggingMessage(params, sessionId);
  }
  /**
   * Sends a resource list changed event to the client, if connected.
   */
  sendResourceListChanged() {
    if (this.isConnected()) {
      this.server.sendResourceListChanged();
    }
  }
  /**
   * Sends a tool list changed event to the client, if connected.
   */
  sendToolListChanged() {
    if (this.isConnected()) {
      this.server.sendToolListChanged();
    }
  }
  /**
   * Sends a prompt list changed event to the client, if connected.
   */
  sendPromptListChanged() {
    if (this.isConnected()) {
      this.server.sendPromptListChanged();
    }
  }
}
const EMPTY_OBJECT_JSON_SCHEMA = {
  type: "object",
  properties: {}
};
function isZodRawShape(obj) {
  if (typeof obj !== "object" || obj === null)
    return false;
  const isEmptyObject = Object.keys(obj).length === 0;
  return isEmptyObject || Object.values(obj).some(isZodTypeLike);
}
function isZodTypeLike(value) {
  return value !== null && typeof value === "object" && "parse" in value && typeof value.parse === "function" && "safeParse" in value && typeof value.safeParse === "function";
}
function promptArgumentsFromSchema(schema) {
  return Object.entries(schema.shape).map(([name, field]) => ({
    name,
    description: field.description,
    required: !field.isOptional()
  }));
}
function createCompletionResult(suggestions) {
  return {
    completion: {
      values: suggestions.slice(0, 100),
      total: suggestions.length,
      hasMore: suggestions.length > 100
    }
  };
}
const EMPTY_COMPLETION_RESULT = {
  completion: {
    values: [],
    hasMore: false
  }
};
async function makeApiRequest(endpoint, data) {
  const response = await fetch(window.wpApiSettings.root + endpoint, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-WP-Nonce": (window.wpApiSettings?.nonce) || ""
    },
    body: JSON.stringify(data)
  });
  const json = await response.json();
  if (!response.ok) {
    if (json && json.message) {
      throw new Error(json.message);
    }
    throw new Error(`HTTP error! status: ${response.status}`);
  }
  return json;
}
function createHFEMCPServer() {
  return new McpServer(
    {
      name: "hfe-angie",
      version: "1.0.0"
    },
    {
      capabilities: {
        tools: {}
      }
    }
  );
}
async function registerAbilities(server) {
  const rest_path = "hfe/v1/angie/";
  const abilities = await makeApiRequest(rest_path + "abilities/", {});
  if (!Array.isArray(abilities)) {
    throw new Error("Abilities response was not an array");
  }
  abilities.forEach((ability) => {
    const input_schema = jsonSchemaToZod(ability.input_schema);
    server.registerTool(
      ability.name,
      {
        title: ability.label,
        description: ability.description,
        inputSchema: input_schema
      },
      async (args) => {
        const response = await makeApiRequest(rest_path + ability.name + "/", args);
        if (response && response.error) {
          return {
            content: [
              {
                type: "text",
                text: "Error: " + (response.message || "Unknown error")
              }
            ],
            isError: true
          };
        }
        return {
          content: [
            {
              type: "text",
              text: JSON.stringify(response, null, 2)
            }
          ]
        };
      }
    );
  });
}
function jsonSchemaToZod(json_schema) {
  const result = {};
  if (json_schema && json_schema.type === "object" && json_schema.properties && typeof json_schema.properties === "object") {
    for (const [key, value] of Object.entries(json_schema.properties)) {
      const prop = value;
      let schema;
      if (prop.type === "object" && prop.properties) {
        schema = objectType(jsonSchemaToZod(prop));
      } else if (prop.enum && Array.isArray(prop.enum) && prop.enum.length > 0) {
        const all_numbers = prop.enum.every((v) => typeof v === "number");
        const all_strings = prop.enum.every((v) => typeof v === "string");
        if (all_strings) {
          schema = enumType(prop.enum);
        } else if (all_numbers) {
          const literals = prop.enum.map((v) => literalType(v));
          if (literals.length === 1) {
            schema = literals[0];
          } else {
            schema = unionType([
              literals[0],
              literals[1],
              ...literals.slice(2)
            ]);
          }
        } else {
          schema = anyType();
        }
      } else {
        switch (prop.type) {
          case "number":
          case "integer":
            schema = numberType();
            break;
          case "string":
            schema = stringType();
            break;
          case "boolean":
            schema = booleanType();
            break;
          case "array":
            schema = arrayType(anyType());
            break;
          case "object":
            schema = recordType(stringType(), anyType());
            break;
          default:
            schema = anyType();
        }
      }
      if (prop.default !== undefined) {
        schema = schema.default(prop.default);
      }
      if (prop.description) {
        schema = schema.describe(prop.description);
      }
      result[key] = schema;
    }
  }
  return result;
}
const init = async () => {
  try {
    const server = createHFEMCPServer();
    await registerAbilities(server);
    const sdk = new ws();
    await sdk.registerServer({
      name: "hfe-angie",
      version: "1.0.0",
      description: "UAE MCP Server for Angie — manage headers, footers, templates, pages, and widgets.",
      server
    });
    console.log("HFE Angie MCP Server registered successfully");
  } catch (error) {
    console.error("Failed to register HFE Angie MCP Server:", error);
  }
};
init();
