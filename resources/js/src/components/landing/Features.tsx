import { 
  Store,
  FileText,
  Calculator,
  Bell,
  Wallet,
  TrendingUp,
  FileSpreadsheet,
  Shield,
  User2,
  Rocket,
  Lock
} from "lucide-react";

const Features = () => {
  const features = [
    {
      icon: Store,
      title: "إدارة التجار المتكاملة",
      description: "نظام متكامل لإدارة بيانات التجار والمحلات مع دعم تعدد الفروع والتصنيفات",
      color: "text-blue-500",
      bgColor: "bg-blue-500/10",
    },
    {
      icon: FileText,
      title: "نظام الطلبات والفواتير",
      description: "إنشاء فواتير وطلبات بسهولة مع ترقيم تلقائي وحساب إجماليات فوري",
      color: "text-green-500",
      bgColor: "bg-green-500/10",
    },
    {
      icon: Calculator,
      title: "النظام المحاسبي المتقدم",
      description: "قيود محاسبية تلقائية وكشف حساب فوري مع سجل زمني تفصيلي",
      color: "text-purple-500",
      bgColor: "bg-purple-500/10",
    },
    {
      icon: Bell,
      title: "التنبيهات المالية الذكية",
      description: "تحذيرات عند تجاوز الحد الائتماني مع حساب نسبة الدين تلقائياً",
      color: "text-amber-500",
      bgColor: "bg-amber-500/10",
    },
    {
      icon: Wallet,
      title: "إدارة المدفوعات",
      description: "دعم جميع طرق الدفع مع ربط بالحسابات البنكية وتحديث فوري للأرصدة",
      color: "text-cyan-500",
      bgColor: "bg-cyan-500/10",
    },
    {
      icon: TrendingUp,
      title: "نظام الميزانية الشخصية",
      description: "تتبع الإنفاق الشهري مع تصنيف المصروفات وتنبيهات الميزانية",
      color: "text-rose-500",
      bgColor: "bg-rose-500/10",
    },
    {
      icon: FileSpreadsheet,
      title: "التقارير والتصدير",
      description: "تصدير البيانات لـ Excel و JSON مع توقيع رقمي وإحصائيات مفصلة",
      color: "text-indigo-500",
      bgColor: "bg-indigo-500/10",
    },
    {
      icon: Shield,
      title: "الأمان والخصوصية",
      description: "نظام صلاحيات متقدم مع تشفير البيانات وسجل تدقيق كامل",
      color: "text-emerald-500",
      bgColor: "bg-emerald-500/10",
    },
  ];

  return (
    <section id="features" className="py-20 px-4 bg-muted/30">
      <div className="container mx-auto">
        {/* Section Header */}
        <div className="text-center max-w-3xl mx-auto mb-16 animate-scale-in">
          <div className="inline-flex items-center gap-2 px-4 py-2 bg-primary/10 rounded-full text-sm font-medium text-primary mb-4">
            المميزات
          </div>
          <h2 className="text-4xl lg:text-5xl font-black mb-6">
            كل ما تحتاجه لإدارة
            <span className="text-gradient"> حسابات احترافية</span>
          </h2>
          <p className="text-xl text-muted-foreground">
            نوفر لك مجموعة شاملة من الأدوات المتقدمة لإدارة أعمالك المالية بكفاءة ودقة
          </p>
        </div>

        {/* Features Grid */}
        <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
          {features.map((feature, index) => {
            const Icon = feature.icon;
            return (
              <div
                key={index}
                className="card-gradient rounded-2xl p-6 border border-border hover-lift animate-scale-in bg-white/80 dark:bg-gradient-to-b dark:from-[#23272b] dark:to-[#181c20] dark:bg-[#181c20]/95 dark:border-[#2d3238] dark:shadow-2xl"
                style={{ animationDelay: `${index * 0.1}s` }}
              >
                <div
                  className={`w-12 h-12 rounded-xl flex items-center justify-center mb-4 ${feature.bgColor} ${
                    feature.bgColor === "bg-blue-500/10" ? "dark:bg-blue-500/20" :
                    feature.bgColor === "bg-green-500/10" ? "dark:bg-green-500/20" :
                    feature.bgColor === "bg-purple-500/10" ? "dark:bg-purple-500/20" :
                    feature.bgColor === "bg-amber-500/10" ? "dark:bg-amber-500/20" :
                    feature.bgColor === "bg-cyan-500/10" ? "dark:bg-cyan-500/20" :
                    feature.bgColor === "bg-rose-500/10" ? "dark:bg-rose-500/20" :
                    feature.bgColor === "bg-indigo-500/10" ? "dark:bg-indigo-500/20" :
                    feature.bgColor === "bg-emerald-500/10" ? "dark:bg-emerald-500/20" :
                    ""
                  }`}
                >
                  <Icon className={`w-6 h-6 ${feature.color}`} />
                </div>
                <h3 className="text-lg font-bold mb-2">{feature.title}</h3>
                <p className="text-sm text-muted-foreground leading-relaxed">
                  {feature.description}
                </p>
              </div>
            );
          })}
        </div>

        {/* Additional Info */}
        <div className="mt-16 text-center">
          <div className="inline-flex flex-col sm:flex-row gap-4 sm:gap-8 bg-card dark:bg-[#181c20]/90 rounded-2xl p-6 shadow-lg dark:shadow-xl border border-border dark:border-[#23272b]">
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
                <Lock className="text-secondary w-6 h-6" />
              </div>
              <div className="text-right">
                <div className="text-sm font-semibold">أمان متقدم</div>
                <div className="text-xs text-muted-foreground">حماية بياناتك أولويتنا</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

export default Features;
