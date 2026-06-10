import {
  Store,
  ShoppingCart,
  Package,
  RotateCcw,
  BookOpen,
  BarChart3,
  ReceiptText,
  Tag,
  TreePine,
  CalendarCheck,
  Wallet,
  FileText,
  TrendingDown,
  Users,
  Shield,
  Rocket,
  User2,
} from "lucide-react";
import { useState } from "react";

// ─── Data ────────────────────────────────────────────────────────

const merchantFeatures = [
  {
    icon: ShoppingCart,
    title: "نقطة البيع (POS)",
    description: "واجهة كاشير سريعة تدعم الباركود والفواتير الحرارية 58/80mm مع حاسبة الصرف الأجنبي.",
    color: "text-amber-500",
    bgColor: "bg-amber-500/10",
  },
  {
    icon: ReceiptText,
    title: "فاتورة حرارية مع QR Code",
    description: "فاتورة حرارية احترافية بمقاسات 58mm/80mm مع رمز QR وفق معايير ZATCA المرحلة الأولى — يظهر للتجار أصحاب الرقم الضريبي فقط.",
    color: "text-emerald-500",
    bgColor: "bg-emerald-500/10",
  },
  {
    icon: Package,
    title: "إدارة المنتجات والمخزون",
    description: "كتالوج منتجات متكامل مع تتبع المخزون لحظياً وطباعة ملصقات الباركود الحرارية.",
    color: "text-purple-500",
    bgColor: "bg-purple-500/10",
  },
  {
    icon: RotateCcw,
    title: "المرتجعات والاستبدال",
    description: "إدارة عمليات الإرجاع والاستبدال مع تحديث تلقائي للمخزون والرصيد المالي.",
    color: "text-rose-500",
    bgColor: "bg-rose-500/10",
  },
  {
    icon: BookOpen,
    title: "القيود المحاسبية",
    description: "قيود يومية يدوية وتلقائية مع دفتر الأستاذ العام وكشف الميزان.",
    color: "text-indigo-500",
    bgColor: "bg-indigo-500/10",
  },
  {
    icon: TreePine,
    title: "شجرة الحسابات",
    description: "دليل حسابات هرمي مخصص مبني على المعايير المحاسبية الدولية.",
    color: "text-lime-600",
    bgColor: "bg-lime-500/10",
  },
  {
    icon: BarChart3,
    title: "إحصائيات ولوحة تحليلية",
    description: "تقارير مبيعات وربحية تفاعلية مع رسوم بيانية متقدمة وتصدير Excel.",
    color: "text-cyan-500",
    bgColor: "bg-cyan-500/10",
  },
  {
    icon: CalendarCheck,
    title: "الإغلاق المحاسبي السنوي",
    description: "إغلاق السنة المالية بنقرة واحدة مع نقل الأرصدة الختامية تلقائياً.",
    color: "text-slate-500",
    bgColor: "bg-slate-500/10",
  },
];

const customerFeatures = [
  {
    icon: TrendingDown,
    title: "متابعة المديونيات",
    description: "اطّلع على ديونك لدى كل تاجر وتاريخ الاستحقاق ومعدل السداد.",
    color: "text-rose-500",
    bgColor: "bg-rose-500/10",
  },
  {
    icon: FileText,
    title: "كشوف الحساب المشتركة",
    description: "تلقّ كشف حسابك مباشرةً من التاجر وراجعه في أي وقت.",
    color: "text-blue-500",
    bgColor: "bg-blue-500/10",
  },
  {
    icon: BarChart3,
    title: "لوحة مقارنة التجار",
    description: "قارن إنفاقك بين التجار بصرياً واتخذ القرار المالي الصحيح.",
    color: "text-purple-500",
    bgColor: "bg-purple-500/10",
  },
  {
    icon: Wallet,
    title: "الميزانية الشخصية",
    description: "حدّد ميزانية شهرية لكل تاجر وتلقّ تنبيهات عند الاقتراب من الحد.",
    color: "text-emerald-500",
    bgColor: "bg-emerald-500/10",
  },
  {
    icon: Store,
    title: "إدارة التجار",
    description: "أضف تجارك وصنّفهم وتتبع معاملاتك معهم في مكان واحد.",
    color: "text-amber-500",
    bgColor: "bg-amber-500/10",
  },
  {
    icon: Users,
    title: "بيانات الحساب الشخصي",
    description: "أدر ملفك الشخصي بأمان مع تصدير واستيراد بياناتك الكاملة.",
    color: "text-cyan-500",
    bgColor: "bg-cyan-500/10",
  },
];

type Tab = "merchant" | "customer";

// ─── Component ───────────────────────────────────────────────────

const Features = () => {
  const [activeTab, setActiveTab] = useState<Tab>("merchant");

  const features = activeTab === "merchant" ? merchantFeatures : customerFeatures;

  return (
    <section id="features" className="py-20 px-4 bg-muted/30 dark:bg-transparent relative overflow-hidden">
      {/* Dark mesh background */}
      <div className="absolute inset-0 dark-mesh pointer-events-none" />
      <div className="container mx-auto relative z-10">
        {/* ── Section Header ── */}
        <div className="text-center max-w-3xl mx-auto mb-10 animate-scale-in">
          <div className="inline-flex items-center gap-2 px-4 py-2 bg-primary/10 rounded-full text-sm font-medium text-primary mb-4">
            المميزات
          </div>
          <h2 className="text-4xl lg:text-5xl font-black mb-4">
            كل ما تحتاجه لإدارة
            <span className="text-gradient"> حسابات احترافية</span>
          </h2>
          <p className="text-xl text-muted-foreground">
            نظام واحد يخدم التاجر في بيعه ومحاسبته، والعميل في متابعة حساباته وإنفاقه
          </p>
        </div>

        {/* ── Tab Switcher ── */}
        <div className="flex justify-center mb-10">
          <div className="inline-flex rounded-2xl bg-muted/60 p-1.5 gap-1.5 border border-border shadow-inner">
            <button
              onClick={() => setActiveTab("merchant")}
              className={`flex items-center gap-2 px-6 py-2.5 rounded-xl font-bold text-sm transition-all duration-300 ${
                activeTab === "merchant"
                  ? "bg-gradient-to-r from-amber-500 to-orange-500 text-white shadow-md scale-[1.03]"
                  : "text-muted-foreground hover:text-foreground hover:bg-background/60"
              }`}
            >
              <Store className="w-4 h-4" />
              مميزات التاجر
            </button>
            <button
              onClick={() => setActiveTab("customer")}
              className={`flex items-center gap-2 px-6 py-2.5 rounded-xl font-bold text-sm transition-all duration-300 ${
                activeTab === "customer"
                  ? "bg-gradient-to-r from-blue-500 to-indigo-500 text-white shadow-md scale-[1.03]"
                  : "text-muted-foreground hover:text-foreground hover:bg-background/60"
              }`}
            >
              <Users className="w-4 h-4" />
              مميزات العميل
            </button>
          </div>
        </div>

        {/* ── Features Grid ── */}
        <div
          key={activeTab}
          className="grid md:grid-cols-2 lg:grid-cols-4 gap-6 animate-scale-in"
        >
          {features.map((feature, index) => {
            const Icon = feature.icon;
            return (
              <div
                key={index}
                className="group relative rounded-2xl p-6 border border-border hover-lift transition-all duration-300
                  bg-white dark:bg-[hsl(222,18%,13%)] dark:border-[hsl(225,25%,22%)]
                  dark:hover:border-[hsl(225,60%,50%)/40%] dark:shadow-[0_4px_20px_hsl(222,18%,4%/0.6)]
                  dark:hover:shadow-[0_8px_30px_hsl(225,70%,60%/0.15)]"
                style={{ animationDelay: `${index * 0.07}s` }}
              >
                {/* Subtle glow on hover in dark mode */}
                <div className="absolute inset-0 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500 dark:bg-gradient-to-br dark:from-primary/5 dark:to-transparent pointer-events-none" />
                <div className={`relative w-12 h-12 rounded-xl flex items-center justify-center mb-4 ${feature.bgColor} dark:bg-opacity-20`}>
                  <Icon className={`w-6 h-6 ${feature.color}`} />
                </div>
                <h3 className="relative text-lg font-bold mb-2 text-foreground">{feature.title}</h3>
                <p className="relative text-sm text-muted-foreground leading-relaxed">{feature.description}</p>
              </div>
            );
          })}
        </div>

        {/* ── Footer Badges ── */}
        <div className="mt-14 text-center">
          <div className="inline-flex flex-col sm:flex-row gap-4 sm:gap-8 bg-card rounded-2xl p-6 shadow-lg border border-border dark:bg-[hsl(222,18%,13%)] dark:border-[hsl(225,25%,22%)] dark:shadow-[0_4px_24px_hsl(222,18%,4%/0.6)]">
            <div className="flex items-center gap-3">
              <div className="w-10 h-10 rounded-full bg-success/10 flex items-center justify-center">
                <User2 className="text-success w-6 h-6" />
              </div>
              <div className="text-right">
                <div className="text-sm font-semibold">دعم فني 24/7</div>
                <div className="text-xs text-muted-foreground">نحن دائماً معك</div>
              </div>
            </div>
            <div className="flex items-center gap-3">
              <div className="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                <Rocket className="text-primary w-6 h-6" />
              </div>
              <div className="text-right">
                <div className="text-sm font-semibold">تحديثات مستمرة</div>
                <div className="text-xs text-muted-foreground">ميزات جديدة شهرياً</div>
              </div>
            </div>
            <div className="flex items-center gap-3">
              <div className="w-10 h-10 rounded-full bg-secondary/10 flex items-center justify-center">
                <Shield className="text-secondary w-6 h-6" />
              </div>
              <div className="text-right">
                <div className="text-sm font-semibold">أمان متقدم</div>
                <div className="text-xs text-muted-foreground">حماية بياناتك أولويتنا</div>
              </div>
            </div>
            <div className="flex items-center gap-3">
              <div className="w-10 h-10 rounded-full bg-amber-500/10 flex items-center justify-center">
                <Tag className="text-amber-500 w-6 h-6" />
              </div>
              <div className="text-right">
                <div className="text-sm font-semibold">QR Code ضريبي</div>
                <div className="text-xs text-muted-foreground">المرحلة الأولى من ZATCA</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

export default Features;
