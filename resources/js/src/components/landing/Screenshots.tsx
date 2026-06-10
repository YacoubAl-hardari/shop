import { Badge } from "@/components/ui/badge";
import {
  BarChart3,
  ShoppingCart,
  FileText,
  Package,
  RotateCcw,
  BookOpen,
  TreePine,
  ReceiptText,
  Users,
  Wallet,
  LayoutDashboard,
  Store,
  ChevronLeft,
  ChevronRight,
} from "lucide-react";
import { useState, useEffect, useCallback } from "react";

// ────────────────────────────── DATA ──────────────────────────────

const merchantScreenshots = [
  {
    id: "pos",
    title: "نقطة البيع (POS)",
    description: "واجهة كاشير سريعة وسهلة الاستخدام مع دعم الباركود والبحث الفوري في المنتجات، وإنجاز الفواتير في ثوانٍ.",
    image: "/merchant/نقطة البيع.png",
    icon: ShoppingCart,
    color: "from-amber-500 to-orange-500",
    features: ["بحث فوري بالاسم / الباركود", "مسح الباركود بالكاميرا", "حاسبة الفكة والصرف الأجنبي"],
  },
  {
    id: "pos-receipt",
    title: "فاتورة مبيعات POS",
    description: "فاتورة حرارية احترافية بمقاسَي 58mm و80mm، تحتوي على رمز QR وفق متطلبات هيئة الزكاة (المرحلة الأولى) ويظهر تلقائياً للتجار الذين لديهم رقم ضريبي.",
    image: "/merchant/فاتورة مبيعات POS.png",
    icon: ReceiptText,
    color: "from-emerald-500 to-teal-500",
    features: ["QR Code وفق معايير ZATCA المرحلة الأولى", "مقاسات حرارية 58mm / 80mm", "يظهر للتجار أصحاب الرقم الضريبي فقط"],
  },
  {
    id: "pos-sales",
    title: "فواتير البيع",
    description: "سجل كامل لجميع عمليات البيع مع إمكانية البحث والتصفية وعرض التفاصيل وطباعة الفاتورة في أي وقت.",
    image: "/merchant/فواتير البيع.png",
    icon: FileText,
    color: "from-blue-500 to-indigo-500",
    features: ["بحث وتصفية متقدم", "إعادة طباعة الفاتورة", "تتبع حالة السداد"],
  },
  {
    id: "products",
    title: "إدارة المنتجات",
    description: "كتالوج منتجات متكامل مع إدارة الأسعار والمخزون ودعم الباركود وطباعة الملصقات الحرارية مباشرةً.",
    image: "/merchant/المنتجات.png",
    icon: Package,
    color: "from-purple-500 to-violet-500",
    features: ["طباعة ملصقات الباركود", "تتبع المخزون لحظياً", "دعم متعدد الوحدات"],
  },
  {
    id: "stock",
    title: "حركات المخزون",
    description: "سجل تفصيلي لكل حركات المخزون من استلام وصرف وتسوية مع تحديد سبب كل حركة.",
    image: "/merchant/حركات المخزون.png",
    icon: RotateCcw,
    color: "from-cyan-500 to-sky-500",
    features: ["سجل زمني كامل", "أنواع حركات متعددة", "تسوية المخزون يدوياً"],
  },
  {
    id: "returns",
    title: "إرجاع واستبدال البضاعة",
    description: "إدارة عمليات الإرجاع والاستبدال بسهولة مع تحديث تلقائي للمخزون والرصيد.",
    image: "/merchant/إرجاع  استبدال بضاعة.png",
    icon: RotateCcw,
    color: "from-rose-500 to-pink-500",
    features: ["إرجاع جزئي أو كلي", "تحديث المخزون تلقائياً", "ربط بالفاتورة الأصلية"],
  },
  {
    id: "accounts-tree",
    title: "شجرة الحسابات",
    description: "دليل حسابات محاسبي متكامل مبني على المعايير المحاسبية مع إمكانية التخصيص الكامل.",
    image: "/merchant/شجرة الحسابات.png",
    icon: TreePine,
    color: "from-lime-500 to-green-500",
    features: ["دليل حسابات هرمي", "تخصيص كامل للحسابات", "مطابق للمعايير المحاسبية"],
  },
  {
    id: "journal",
    title: "القيود اليومية",
    description: "تسجيل القيود المحاسبية بدقة عالية مع إمكانية العرض والتدقيق ومراجعة القيود التلقائية.",
    image: "/merchant/القيود اليومية.png",
    icon: BookOpen,
    color: "from-orange-500 to-amber-500",
    features: ["قيود يدوية وتلقائية", "مراجعة وتدقيق كامل", "ربط بمراكز التكلفة"],
  },
  {
    id: "stats",
    title: "لوحة الإحصائيات",
    description: "لوحة تحكم إحصائية شاملة ببيانات المبيعات والمخزون والربحية مع رسوم بيانية تفاعلية.",
    image: "/merchant/لوحة الإحصائيات.png",
    icon: BarChart3,
    color: "from-violet-500 to-purple-500",
    features: ["رسوم بيانية تفاعلية", "مقارنة الفترات الزمنية", "تصدير التقارير"],
  },
  {
    id: "merchant-data",
    title: "إدارة بيانات التاجر",
    description: "إعداد الملف التجاري الكامل بما يشمل الشعار ومعلومات الضريبة والبيانات القانونية.",
    image: "/merchant/إدارة بيانات التاجر.png",
    icon: Store,
    color: "from-teal-500 to-emerald-500",
    features: ["الرقم الضريبي ودعم ZATCA", "الشعار والهوية البصرية", "إعدادات الفروع"],
  },
  {
    id: "fiscal-year",
    title: "الإغلاق المحاسبي السنوي",
    description: "إغلاق السنة المالية بنقرة واحدة مع نقل الأرصدة الختامية إلى السنة الجديدة تلقائياً.",
    image: "/merchant/الإغلاق المحاسبي السنوي.png",
    icon: BookOpen,
    color: "from-slate-500 to-gray-600",
    features: ["إغلاق تلقائي للحسابات", "نقل أرصدة ختامية", "تقرير ختام السنة"],
  },
];

const customerScreenshots = [
  {
    id: "customer-dashboard",
    title: "لوحة التحكم",
    description: "نظرة شاملة على جميع معاملاتك المالية مع التجار، أرصدتك، مديونياتك، وآخر العمليات بشكل واضح ومنظم.",
    image: "/customer/لوحة التحكم.png",
    icon: LayoutDashboard,
    color: "from-blue-500 to-indigo-500",
    features: ["ملخص مالي شامل", "آخر العمليات الفورية", "تنبيهات ذكية"],
  },
  {
    id: "merchants",
    title: "التجار",
    description: "قائمة بجميع التجار المرتبطين بك مع رصيد كل تاجر ومستوى المديونية وآخر عملية.",
    image: "/customer/التجار.png",
    icon: Store,
    color: "from-amber-500 to-orange-500",
    features: ["عرض الديون والأرصدة", "تاريخ آخر عملية", "تصنيفات التجار"],
  },
  {
    id: "merchant-comparison",
    title: "لوحة مقارنة التجار",
    description: "مقارنة بصرية ذكية بين تجارك في الإنفاق والمديونية والمعاملات لمساعدتك في اتخاذ القرار المالي الصحيح.",
    image: "/customer/لوحة مقارنة التجار.png",
    icon: BarChart3,
    color: "from-purple-500 to-violet-500",
    features: ["مقارنة بصرية بالرسوم", "تحليل الإنفاق الشهري", "إحصائيات مفصلة"],
  },
  {
    id: "shared-statements",
    title: "كشوف الحساب المشتركة",
    description: "تلقّ كشف حسابك من التاجر مباشرةً وراجع رصيدك لدى كل تاجر في أي وقت بكل شفافية.",
    image: "/customer/كشوف الحساب المشتركة.png",
    icon: FileText,
    color: "from-green-500 to-teal-500",
    features: ["كشف حساب فوري", "مشاركة من التاجر مباشرة", "سجل كامل للمعاملات"],
  },
  {
    id: "financial-settings",
    title: "إدارة إعداداتك المالية",
    description: "تحكّم في حدود إنفاقك الشهري، صنّف تجارك، وضع ميزانية شخصية لكل فئة.",
    image: "/customer/إدارة إعداداتك المالية.png",
    icon: Wallet,
    color: "from-rose-500 to-pink-500",
    features: ["ميزانية شخصية", "حدود ائتمانية مخصصة", "تصنيف التجار"],
  },
  {
    id: "personal-data",
    title: "إدارة بياناتك الشخصية",
    description: "حافظ على بياناتك الشخصية محدّثة وآمنة مع إمكانية تصدير واستيراد بياناتك الكاملة.",
    image: "/customer/إدارة بياناتك الشخصية.png",
    icon: Users,
    color: "from-cyan-500 to-sky-500",
    features: ["تصدير واستيراد البيانات", "حماية الحساب", "إدارة الصلاحيات"],
  },
];

// ────────────────────────────── TYPES ──────────────────────────────

type Tab = "merchant" | "customer";

// ────────────────────────────── COMPONENT ──────────────────────────────

const Screenshots = () => {
  const [activeTab, setActiveTab] = useState<Tab>("merchant");
  const [activeSlide, setActiveSlide] = useState(0);
  const [isAutoPlaying, setIsAutoPlaying] = useState(true);
  const [isTransitioning, setIsTransitioning] = useState(false);

  const screenshots = activeTab === "merchant" ? merchantScreenshots : customerScreenshots;
  const current = screenshots[activeSlide] ?? screenshots[0];
  const Icon = current.icon;

  const switchTab = (tab: Tab) => {
    if (tab === activeTab) return;
    setIsTransitioning(true);
    setTimeout(() => {
      setActiveTab(tab);
      setActiveSlide(0);
      setIsAutoPlaying(true);
      setIsTransitioning(false);
    }, 250);
  };

  const goToSlide = useCallback(
    (index: number) => {
      if (index === activeSlide || isTransitioning) return;
      setIsTransitioning(true);
      setTimeout(() => {
        setActiveSlide(index);
        setTimeout(() => setIsTransitioning(false), 80);
      }, 120);
    },
    [activeSlide, isTransitioning]
  );

  const nextSlide = useCallback(() => {
    setIsAutoPlaying(false);
    const next = (activeSlide + 1) % screenshots.length;
    goToSlide(next);
  }, [activeSlide, screenshots.length, goToSlide]);

  const prevSlide = useCallback(() => {
    setIsAutoPlaying(false);
    const prev = (activeSlide - 1 + screenshots.length) % screenshots.length;
    goToSlide(prev);
  }, [activeSlide, screenshots.length, goToSlide]);

  // Auto-play
  useEffect(() => {
    if (!isAutoPlaying) return;
    const id = setInterval(() => {
      const next = (activeSlide + 1) % screenshots.length;
      goToSlide(next);
    }, 4500);
    return () => clearInterval(id);
  }, [isAutoPlaying, activeSlide, screenshots.length, goToSlide]);

  return (
    <section id="screenshots" className="py-20 relative overflow-hidden">
      {/* Background */}
      <div className="absolute inset-0 bg-gradient-to-b from-background via-accent/5 to-background" />

      <div className="container mx-auto px-4 relative">
        {/* ── Section Header ── */}
        <div className="text-center max-w-3xl mx-auto mb-12 animate-fade-in">
          <Badge className="mb-4" variant="outline">
            استكشف النظام
          </Badge>
          <h2 className="text-4xl md:text-5xl font-bold mb-4">
            واجهات <span className="text-gradient">احترافية وسهلة</span> الاستخدام
          </h2>
          <p className="text-lg text-muted-foreground">
            نظام واحد يخدم التاجر والعميل — اختر الدور لاستعراض إمكانياته
          </p>
        </div>

        {/* ── Tab Switcher ── */}
        <div className="flex justify-center mb-10">
          <div className="inline-flex rounded-2xl bg-muted/60 p-1.5 gap-1.5 border border-border shadow-inner">
            {/* Merchant Tab */}
            <button
              id="tab-merchant"
              onClick={() => switchTab("merchant")}
              className={`relative flex items-center gap-2.5 px-6 py-3 rounded-xl font-bold text-sm transition-all duration-300 ${
                activeTab === "merchant"
                  ? "bg-gradient-to-r from-amber-500 to-orange-500 text-white shadow-lg scale-[1.03]"
                  : "text-muted-foreground hover:text-foreground hover:bg-background/60"
              }`}
            >
              <Store className="w-4 h-4 flex-shrink-0" />
              <span>للتاجر</span>
              {activeTab === "merchant" && (
                <span className="absolute -top-1 -right-1 w-2.5 h-2.5 bg-green-400 rounded-full animate-pulse border-2 border-white" />
              )}
            </button>

            {/* Customer Tab */}
            <button
              id="tab-customer"
              onClick={() => switchTab("customer")}
              className={`relative flex items-center gap-2.5 px-6 py-3 rounded-xl font-bold text-sm transition-all duration-300 ${
                activeTab === "customer"
                  ? "bg-gradient-to-r from-blue-500 to-indigo-500 text-white shadow-lg scale-[1.03]"
                  : "text-muted-foreground hover:text-foreground hover:bg-background/60"
              }`}
            >
              <Users className="w-4 h-4 flex-shrink-0" />
              <span>للعميل</span>
              {activeTab === "customer" && (
                <span className="absolute -top-1 -right-1 w-2.5 h-2.5 bg-green-400 rounded-full animate-pulse border-2 border-white" />
              )}
            </button>
          </div>
        </div>

        {/* ── Role Description Banner ── */}
        <div
          className={`max-w-2xl mx-auto mb-10 rounded-2xl border border-border p-4 text-center text-sm transition-all duration-500 ${
            activeTab === "merchant"
              ? "bg-amber-50/60 dark:bg-amber-950/20 border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300"
              : "bg-blue-50/60 dark:bg-blue-950/20 border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-300"
          }`}
        >
          {activeTab === "merchant" ? (
            <>
              <span className="font-bold">🏪 أنت تاجر؟</span> — تحكّم في مخزونك، أصدر فواتير احترافية، تابع مبيعاتك، وادر حساباتك المالية بدقة عالية.
            </>
          ) : (
            <>
              <span className="font-bold">👤 أنت عميل؟</span> — راجع مديونياتك وأرصدتك لدى التجار، قارن إنفاقك، واستلم كشوف الحساب فورياً.
            </>
          )}
        </div>

        {/* ── Main Showcase ── */}
        <div
          className={`max-w-7xl mx-auto transition-all duration-300 ${
            isTransitioning ? "opacity-0 scale-[0.98]" : "opacity-100 scale-100"
          }`}
        >
          <div className="grid lg:grid-cols-2 gap-10 items-center">
            {/* Left – Info */}
            <div className="order-2 lg:order-1 space-y-6">
              {/* Icon + Title */}
              <div className="flex items-start gap-4">
                <div className={`p-3.5 rounded-2xl bg-gradient-to-br ${current.color} shadow-lg flex-shrink-0`}>
                  <Icon className="w-7 h-7 text-white" />
                </div>
                <div className="flex-1">
                  <h3 className="text-2xl md:text-3xl font-black mb-2">{current.title}</h3>
                  <p className="text-base text-muted-foreground leading-relaxed">{current.description}</p>
                </div>
              </div>

              {/* Feature Bullets */}
              <div className="space-y-3 pr-16">
                {current.features.map((f, i) => (
                  <div key={i} className="flex items-center gap-3">
                    <div className={`w-2.5 h-2.5 rounded-full bg-gradient-to-br ${current.color} flex-shrink-0`} />
                    <span className="text-sm font-medium text-foreground">{f}</span>
                  </div>
                ))}
              </div>

              {/* Navigation Dots */}
              <div className="flex items-center gap-2 pt-2 flex-wrap">
                {screenshots.map((_, idx) => (
                  <button
                    key={idx}
                    onClick={() => { setIsAutoPlaying(false); goToSlide(idx); }}
                    disabled={isTransitioning}
                    className={`h-2 rounded-full transition-all duration-300 disabled:opacity-50 ${
                      idx === activeSlide
                        ? `w-8 bg-gradient-to-r ${current.color} shadow`
                        : "w-2 bg-border hover:bg-muted-foreground/50"
                    }`}
                    aria-label={`الشريحة ${idx + 1}`}
                  />
                ))}
              </div>

              {/* Counter + auto-play toggle */}
              <div className="flex items-center gap-4 text-xs text-muted-foreground">
                <span>{activeSlide + 1} / {screenshots.length}</span>
                <button
                  onClick={() => setIsAutoPlaying(!isAutoPlaying)}
                  className="px-3 py-1 rounded-full bg-muted hover:bg-muted-foreground/20 transition-colors"
                >
                  {isAutoPlaying ? "إيقاف التشغيل التلقائي" : "تشغيل تلقائي"}
                </button>
              </div>
            </div>

            {/* Right – Screenshot */}
            <div className="order-1 lg:order-2 relative group">
              {/* Prev / Next arrows */}
              <button
                onClick={prevSlide}
                disabled={isTransitioning}
                className="absolute left-3 top-1/2 -translate-y-1/2 z-10 p-2.5 rounded-full bg-background/90 backdrop-blur-sm border border-border shadow-lg opacity-0 group-hover:opacity-100 transition-all hover:scale-110 disabled:opacity-30"
                aria-label="السابق"
              >
                <ChevronRight className="w-5 h-5" />
              </button>
              <button
                onClick={nextSlide}
                disabled={isTransitioning}
                className="absolute right-3 top-1/2 -translate-y-1/2 z-10 p-2.5 rounded-full bg-background/90 backdrop-blur-sm border border-border shadow-lg opacity-0 group-hover:opacity-100 transition-all hover:scale-110 disabled:opacity-30"
                aria-label="التالي"
              >
                <ChevronLeft className="w-5 h-5" />
              </button>

              {/* Image Card */}
              <div className={`relative overflow-hidden rounded-2xl border-2 border-border bg-gradient-to-br from-card to-accent/10 p-2 shadow-2xl`}>
                <div className={`absolute inset-0 bg-gradient-to-br ${current.color} opacity-5 pointer-events-none`} />
                <div className="relative rounded-xl overflow-hidden bg-background shadow-lg">
                  <img
                    key={`${activeTab}-${activeSlide}`}
                    src={current.image}
                    alt={current.title}
                    className="w-full h-auto object-cover"
                    loading="lazy"
                  />
                  {/* Bottom gradient for premium feel */}
                  <div className="absolute inset-0 bg-gradient-to-t from-background/30 via-transparent to-transparent pointer-events-none" />
                </div>

                {/* Tab badge overlay */}
                <div className={`absolute top-4 left-4 px-3 py-1 rounded-full text-xs font-bold text-white bg-gradient-to-r ${current.color} shadow-lg`}>
                  {activeTab === "merchant" ? "واجهة التاجر" : "واجهة العميل"}
                </div>
              </div>

              {/* Decorative glows */}
              <div className={`absolute -bottom-6 -right-6 w-40 h-40 bg-gradient-to-br ${current.color} rounded-full blur-3xl -z-10 opacity-20`} />
              <div className={`absolute -top-6 -left-6 w-40 h-40 bg-gradient-to-br ${current.color} rounded-full blur-3xl -z-10 opacity-10`} />
            </div>
          </div>
        </div>

        {/* ── Thumbnail Grid ── */}
        <div className="max-w-6xl mx-auto mt-12">
          <div className="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-6 gap-3">
            {screenshots.map((s, idx) => {
              const TIcon = s.icon;
              const isActive = idx === activeSlide;
              return (
                <button
                  key={`${activeTab}-${idx}`}
                  onClick={() => { setIsAutoPlaying(false); goToSlide(idx); }}
                  disabled={isTransitioning}
                  className={`relative group p-3 rounded-xl border-2 transition-all duration-300 text-center disabled:opacity-50 ${
                    isActive
                      ? "border-primary bg-primary/5 shadow-md scale-105"
                      : "border-border bg-card hover:border-primary/50 hover:shadow hover:scale-105"
                  }`}
                >
                  <div className={`w-9 h-9 rounded-lg bg-gradient-to-br ${s.color} flex items-center justify-center mx-auto mb-1.5 transition-transform duration-300 ${isActive ? "scale-110 shadow" : "group-hover:scale-110"}`}>
                    <TIcon className="w-4 h-4 text-white" />
                  </div>
                  <p className={`text-[10px] font-medium leading-tight transition-colors ${isActive ? "text-foreground" : "text-muted-foreground"}`}>
                    {s.title}
                  </p>
                  {isActive && (
                    <div className={`absolute -bottom-1 left-1/2 -translate-x-1/2 w-2/3 h-0.5 rounded-full bg-gradient-to-r ${s.color}`} />
                  )}
                </button>
              );
            })}
          </div>
        </div>
      </div>
    </section>
  );
};

export default Screenshots;