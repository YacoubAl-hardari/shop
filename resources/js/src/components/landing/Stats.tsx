import { Store, Users, Zap, Shield } from "lucide-react";

const Stats = () => {
  const stats = [
    {
      icon: Store,
      value: "2",
      label: "نوع مستخدم",
      description: "تاجر وعميل — نظام واحد يخدم الجميع",
      color: "text-amber-500",
      bgColor: "bg-amber-500/10",
    },
    {
      icon: Users,
      value: "7+",
      label: "وحدة وظيفية",
      description: "POS · مخزون · محاسبة · مرتجعات وأكثر",
      color: "text-primary",
      bgColor: "bg-primary/10",
    },
    {
      icon: Zap,
      value: "< 3 ثوان",
      label: "وقت الفاتورة",
      description: "من مسح الباركود حتى طباعة الإيصال",
      color: "text-amber-500",
      bgColor: "bg-amber-500/10",
    },
    {
      icon: Shield,
      value: "100%",
      label: "خصوصية البيانات",
      description: "بيانات مستضافة وآمنة بتشفير متقدم",
      color: "text-success",
      bgColor: "bg-success/10",
    },
  ];

  return (
    <section id="stats" className="py-20 px-4 relative overflow-hidden">
      {/* Background */}
      <div className="absolute inset-0 bg-gradient-hero opacity-5 dark:opacity-0" />
      <div className="absolute inset-0 dark-mesh pointer-events-none" />

      <div className="container mx-auto relative z-10">
        {/* Section Header */}
        <div className="text-center max-w-3xl mx-auto mb-16 animate-scale-in">
          <div className="inline-flex items-center gap-2 px-4 py-2 bg-success/10 rounded-full text-sm font-medium text-success mb-4">
            لماذا نظامنا؟
          </div>
          <h2 className="text-4xl lg:text-5xl font-black mb-6">
            نظام مبني على
            <span className="text-gradient"> الواقع الفعلي</span>
          </h2>
          <p className="text-xl text-muted-foreground">
            صُمّم بناءً على احتياجات حقيقية للتجار والعملاء — لا مبالغات ولا وعود فارغة
          </p>
        </div>

        {/* Stats Grid */}
        <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
          {stats.map((stat, index) => {
            const Icon = stat.icon;
            return (
              <div
                key={index}
                className="group relative rounded-2xl p-8 text-center border border-border hover-lift transition-all duration-300
                  bg-white dark:bg-[hsl(222,18%,12%)] dark:border-[hsl(225,25%,20%)]
                  dark:hover:border-[hsl(225,60%,55%)/30%]
                  dark:shadow-[0_4px_20px_hsl(222,18%,4%/0.5)]
                  dark:hover:shadow-[0_8px_32px_hsl(225,70%,60%/0.18)]"
                style={{ animationDelay: `${index * 0.1}s` }}
              >
                <div className="absolute inset-0 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500 dark:bg-gradient-to-br dark:from-primary/5 dark:via-transparent dark:to-transparent pointer-events-none" />
                <div className={`relative w-16 h-16 ${stat.bgColor} rounded-2xl flex items-center justify-center mx-auto mb-4`}>
                  <Icon className={`w-8 h-8 ${stat.color}`} />
                </div>
                <div className="relative text-3xl font-black text-gradient mb-2">
                  {stat.value}
                </div>
                <div className="relative text-lg font-bold mb-2 text-foreground">
                  {stat.label}
                </div>
                <div className="relative text-sm text-muted-foreground">
                  {stat.description}
                </div>
              </div>
            );
          })}
        </div>

        {/* Feature Pills */}
        <div className="mt-16 flex flex-wrap justify-center items-center gap-3">
          {[
            "🏪 نقطة البيع POS",
            "📦 إدارة المخزون",
            "📄 فواتير حرارية",
            "🔖 ملصقات باركود",
            "📊 لوحة إحصائيات",
            "🔁 مرتجعات",
            "📒 قيود محاسبية",
            "🌲 شجرة الحسابات",
            "👤 بوابة العميل",
            "💱 صرف العملات",
          ].map((pill) => (
            <span
              key={pill}
              className="px-4 py-2 rounded-full text-sm font-medium border transition-colors cursor-default
                bg-muted border-border text-foreground
                hover:bg-primary/10 hover:border-primary/40
                dark:bg-[hsl(222,18%,14%)] dark:border-[hsl(225,25%,22%)] dark:text-foreground
                dark:hover:bg-primary/15 dark:hover:border-primary/50"
            >
              {pill}
            </span>
          ))}
        </div>
      </div>
    </section>
  );
};

export default Stats;
