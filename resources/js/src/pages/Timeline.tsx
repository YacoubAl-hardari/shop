import { CheckCircle2, Clock, Rocket, Sparkles, ArrowLeft, Store, ShoppingCart, Package, BookOpen, BarChart3, Users, Smartphone, Link2, Bot } from "lucide-react";
import { Badge } from "@/components/ui/badge";
import { Card } from "@/components/ui/card";
import Header from "@/components/landing/Header";
import Footer from "@/components/landing/Footer";

const Timeline = () => {
  const timelineItems = [
    // ── LAUNCHED ──────────────────────────────────────────
    {
      date: "✅ متاح الآن",
      title: "نظام نقطة البيع (POS)",
      description: "واجهة كاشير سريعة وسهلة تدعم البحث بالاسم والباركود، مع مسح الباركود بالكاميرا وحاسبة الصرف الأجنبي وطباعة الفاتورة الحرارية.",
      status: "launched",
      icon: ShoppingCart,
      iconColor: "text-amber-500",
      iconBg: "bg-amber-500/10",
      features: [
        "بحث فوري بالاسم أو الباركود",
        "مسح الباركود عبر كاميرا المتصفح",
        "حاسبة الفكة وصرف العملات الأجنبية",
        "فاتورة حرارية 58mm / 80mm",
      ],
    },
    {
      date: "✅ متاح الآن",
      title: "إدارة المنتجات والمخزون",
      description: "كتالوج منتجات متكامل مع تتبع المخزون لحظياً، طباعة ملصقات الباركود الحرارية، وتسجيل حركات الإدخال والإخراج والتسوية.",
      status: "launched",
      icon: Package,
      iconColor: "text-purple-500",
      iconBg: "bg-purple-500/10",
      features: [
        "إدارة المنتجات والأصناف والوحدات",
        "تتبع المخزون لحظياً",
        "طباعة ملصقات الباركود الحرارية",
        "سجل تفصيلي لحركات المخزون",
      ],
    },
    {
      date: "✅ متاح الآن",
      title: "الفاتورة الضريبية المبسطة",
      description: "إصدار فواتير بيع احترافية تحتوي على رمز QR مُولَّد محلياً وفق متطلبات هيئة الزكاة والضريبة (المرحلة الأولى)، مع دعم كامل لضريبة القيمة المضافة للتجار الذين لديهم رقم ضريبي.",
      status: "launched",
      icon: BookOpen,
      iconColor: "text-emerald-500",
      iconBg: "bg-emerald-500/10",
      features: [
        "QR Code وفق معايير ZATCA المرحلة الأولى",
        "دعم ضريبة القيمة المضافة 15%",
        "إخفاء القسم الضريبي إن لم يكن للتاجر رقم ضريبي",
        "طباعة مباشرة من المتصفح",
      ],
    },
    {
      date: "✅ متاح الآن",
      title: "النظام المحاسبي المتكامل",
      description: "دليل حسابات هرمي، قيود يومية يدوية وتلقائية، كشف حساب فوري، وإغلاق محاسبي سنوي مع نقل الأرصدة الختامية تلقائياً.",
      status: "launched",
      icon: BookOpen,
      iconColor: "text-indigo-500",
      iconBg: "bg-indigo-500/10",
      features: [
        "شجرة حسابات هرمية قابلة للتخصيص",
        "قيود يومية يدوية وتلقائية",
        "كشف الحساب وميزان المراجعة",
        "إغلاق السنة المالية بنقرة واحدة",
      ],
    },
    {
      date: "✅ متاح الآن",
      title: "بوابة العميل",
      description: "واجهة مخصصة للعميل تمكنه من متابعة مديونياته وأرصدته لدى تجاره، مقارنة الإنفاق، واستلام كشوف الحساب مباشرةً من التاجر.",
      status: "launched",
      icon: Users,
      iconColor: "text-blue-500",
      iconBg: "bg-blue-500/10",
      features: [
        "لوحة تحكم بأرصدة ومديونيات كل تاجر",
        "لوحة مقارنة بصرية بين التجار",
        "كشوف الحساب المشتركة",
        "إدارة الميزانية الشخصية",
      ],
    },
    {
      date: "✅ متاح الآن",
      title: "لوحة الإحصائيات والتقارير",
      description: "تقارير مبيعات وربحية تفاعلية، مع تصدير البيانات ومقارنة الفترات الزمنية لاتخاذ قرارات مالية أفضل.",
      status: "launched",
      icon: BarChart3,
      iconColor: "text-cyan-500",
      iconBg: "bg-cyan-500/10",
      features: [
        "رسوم بيانية تفاعلية للمبيعات",
        "مقارنة الفترات الزمنية",
        "تصدير Excel و JSON",
        "إحصائيات المخزون والأرباح",
      ],
    },
    {
      date: "✅ متاح الآن",
      title: "إدارة التجار والموزعين",
      description: "نظام متكامل لإدارة التجار والعملاء مع تحديد الحدود الائتمانية والتنبيهات الذكية عند الاقتراب منها.",
      status: "launched",
      icon: Store,
      iconColor: "text-orange-500",
      iconBg: "bg-orange-500/10",
      features: [
        "بطاقة تعريفية لكل تاجر",
        "الحدود الائتمانية والتنبيهات",
        "سجل كامل للمعاملات",
        "دعم التجار الموزعين",
      ],
    },

    // ── UPCOMING ──────────────────────────────────────────
    {
      date: "🔜 قريباً",
      title: "التكامل الرسمي مع هيئة الزكاة (ZATCA — المرحلة الثانية)",
      description: "ربط مباشر مع منصة فاتورة الحكومية لإرسال الفواتير إلكترونياً والتوقيع الرقمي المعتمد، حين تستوفي المتطلبات التقنية الكاملة.",
      status: "development",
      icon: Link2,
      iconColor: "text-rose-500",
      iconBg: "bg-rose-500/10",
      features: [
        "توقيع رقمي XML معتمد من هيئة الزكاة",
        "إرسال الفاتورة الإلكترونية لمنصة فاتورة",
        "أرشفة إلكترونية آمنة",
        "تقارير الامتثال الضريبي",
      ],
    },
    {
      date: "🔜 قريباً",
      title: "تطبيق الجوال (iOS & Android)",
      description: "تطبيق محمول كامل لنظامَي iOS و Android مع دعم الإشعارات الفورية ومسح الباركود من كاميرا الجوال.",
      status: "upcoming",
      icon: Smartphone,
      iconColor: "text-violet-500",
      iconBg: "bg-violet-500/10",
      features: [
        "واجهة محسّنة للشاشات الصغيرة",
        "مسح الباركود من كاميرا الجوال",
        "إشعارات فورية للمعاملات",
        "وضع عدم الاتصال (Offline)",
      ],
    },
    {
      date: "🔜 قريباً",
      title: "التقارير الذكية بالذكاء الاصطناعي",
      description: "تحليل تلقائي للأنماط المالية مع توقعات التدفق النقدي وتوصيات مخصصة لتحسين الأداء.",
      status: "upcoming",
      icon: Bot,
      iconColor: "text-sky-500",
      iconBg: "bg-sky-500/10",
      features: [
        "تحليل تلقائي للأنماط المالية",
        "توقعات التدفق النقدي",
        "تنبيهات ذكية للفرص والمخاطر",
        "توصيات مخصصة لكل تاجر",
      ],
    },
  ];

  const getStatusBadge = (status: string) => {
    switch (status) {
      case "launched":
        return (
          <Badge className="bg-success/15 text-success border border-success/30 gap-1">
            <CheckCircle2 className="w-3 h-3" />
            متاح الآن
          </Badge>
        );
      case "development":
        return (
          <Badge className="bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/30 gap-1">
            <Rocket className="w-3 h-3" />
            قيد التطوير
          </Badge>
        );
      default:
        return (
          <Badge variant="outline" className="gap-1">
            <Clock className="w-3 h-3" />
            قريباً
          </Badge>
        );
    }
  };

  const launchedCount = timelineItems.filter((i) => i.status === "launched").length;
  const upcomingCount = timelineItems.filter((i) => i.status !== "launched").length;

  return (
    <>
      <Header />
      <div className="min-h-screen bg-background pt-16">
        {/* ── Hero Section ── */}
        <section className="relative py-20 bg-gradient-hero overflow-hidden">
          <div className="absolute inset-0 bg-[radial-gradient(circle_at_30%_50%,rgba(255,255,255,0.1),transparent_50%)]" />
          <div className="container mx-auto px-4 relative">
            <div className="text-center max-w-3xl mx-auto">
              <div className="inline-flex items-center justify-center p-2 bg-white/10 rounded-full mb-6 backdrop-blur-sm">
                <Sparkles className="w-6 h-6 text-white ml-2" />
                <span className="text-white font-medium px-3">خارطة الطريق</span>
              </div>
              <h1 className="text-4xl md:text-5xl font-bold text-white mb-6">
                ما تم إنجازه وما هو قادم
              </h1>
              <p className="text-xl text-white/90 leading-relaxed mb-8">
                نظام حيّ ومتطور — نعمل باستمرار على إضافة ميزات جديدة لتلبية احتياجاتكم المتنامية
              </p>

              {/* Quick stats */}
              <div className="flex justify-center gap-6 flex-wrap">
                <div className="bg-white/15 backdrop-blur-sm rounded-2xl px-6 py-3 text-white text-center">
                  <div className="text-3xl font-black">{launchedCount}</div>
                  <div className="text-sm text-white/80">ميزة متاحة الآن</div>
                </div>
                <div className="bg-white/15 backdrop-blur-sm rounded-2xl px-6 py-3 text-white text-center">
                  <div className="text-3xl font-black">{upcomingCount}</div>
                  <div className="text-sm text-white/80">ميزة قادمة</div>
                </div>
                <div className="bg-white/15 backdrop-blur-sm rounded-2xl px-6 py-3 text-white text-center">
                  <div className="text-3xl font-black">2</div>
                  <div className="text-sm text-white/80">نوع مستخدم مدعوم</div>
                </div>
              </div>
            </div>
          </div>
        </section>

        {/* ── Timeline Section ── */}
        <section className="py-20">
          <div className="container mx-auto px-4">
            <div className="max-w-4xl mx-auto">

              {/* Legend */}
              <div className="flex flex-wrap justify-center gap-4 mb-12">
                <div className="flex items-center gap-2 text-sm text-muted-foreground">
                  <span className="w-3 h-3 rounded-full bg-success inline-block" />
                  متاح الآن في النظام
                </div>
                <div className="flex items-center gap-2 text-sm text-muted-foreground">
                  <span className="w-3 h-3 rounded-full bg-amber-500 inline-block" />
                  قيد التطوير
                </div>
                <div className="flex items-center gap-2 text-sm text-muted-foreground">
                  <span className="w-3 h-3 rounded-full bg-border inline-block" />
                  مخطط مستقبلاً
                </div>
              </div>

              {/* Timeline */}
              <div className="relative">
                {/* Timeline Line */}
                <div className="hidden md:block absolute right-[50%] top-0 bottom-0 w-0.5 bg-gradient-to-b from-success via-primary to-border" />

                {/* Timeline Items */}
                <div className="space-y-10">
                  {timelineItems.map((item, index) => {
                    const Icon = item.icon;
                    const isLaunched = item.status === "launched";
                    return (
                      <div
                        key={index}
                        className="relative animate-fade-in"
                        style={{ animationDelay: `${index * 0.08}s` }}
                      >
                        {/* Timeline Dot */}
                        <div
                          className={`hidden md:flex absolute right-[50%] top-6 w-5 h-5 -mr-2.5 rounded-full border-4 border-background shadow-lg items-center justify-center ${
                            isLaunched
                              ? "bg-success"
                              : item.status === "development"
                              ? "bg-amber-500"
                              : "bg-border"
                          }`}
                        />

                        {/* Content Card */}
                        <div
                          className={`md:w-[calc(50%-2.5rem)] ${
                            index % 2 === 0 ? "md:mr-auto md:pl-8" : "md:ml-auto md:pr-8"
                          }`}
                        >
                          <Card
                            className={`p-6 hover-lift border-l-4 transition-all ${
                              isLaunched
                                ? "border-l-success"
                                : item.status === "development"
                                ? "border-l-amber-500"
                                : "border-l-border"
                            }`}
                          >
                            {/* Header */}
                            <div className="flex items-start justify-between gap-3 mb-3">
                              <div className="flex items-start gap-3 flex-1">
                                <div className={`w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 ${item.iconBg}`}>
                                  <Icon className={`w-5 h-5 ${item.iconColor}`} />
                                </div>
                                <div>
                                  <p className="text-xs text-muted-foreground mb-1">{item.date}</p>
                                  <h3 className="text-lg font-bold text-foreground leading-snug">
                                    {item.title}
                                  </h3>
                                </div>
                              </div>
                              {getStatusBadge(item.status)}
                            </div>

                            {/* Description */}
                            <p className="text-sm text-muted-foreground mb-4 leading-relaxed pr-13">
                              {item.description}
                            </p>

                            {/* Features List */}
                            <div className="space-y-1.5">
                              {item.features.map((feature, idx) => (
                                <div key={idx} className="flex items-start gap-2">
                                  <CheckCircle2
                                    className={`w-4 h-4 mt-0.5 flex-shrink-0 ${
                                      isLaunched ? "text-success" : "text-muted-foreground"
                                    }`}
                                  />
                                  <span className="text-sm text-foreground">{feature}</span>
                                </div>
                              ))}
                            </div>
                          </Card>
                        </div>
                      </div>
                    );
                  })}
                </div>
              </div>

              {/* CTA */}
              <div className="mt-16 text-center">
                <Card className="p-8 bg-gradient-card">
                  <Sparkles className="w-12 h-12 text-primary mx-auto mb-4" />
                  <h3 className="text-2xl font-bold mb-3">هل لديك اقتراح لميزة جديدة؟</h3>
                  <p className="text-muted-foreground mb-6">
                    نستمع دائماً لمقترحاتكم لتطوير النظام بما يناسب احتياجاتكم الفعلية
                  </p>
                  <a
                    href="mailto:info@example.com"
                    className="inline-flex items-center gap-2 px-6 py-3 bg-gradient-primary text-white rounded-lg font-medium hover:shadow-glow transition-all"
                  >
                    راسلنا باقتراحك
                    <ArrowLeft className="w-4 h-4" />
                  </a>
                </Card>
              </div>
            </div>
          </div>
        </section>
      </div>
      <Footer />
    </>
  );
};

export default Timeline;
