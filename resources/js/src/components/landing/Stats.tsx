import { Users, TrendingUp, Clock, Award } from "lucide-react";

const Stats = () => {
  const stats = [
    {
      icon: Users,
      value: "+5,000",
      label: "تاجر وعميل نشط",
      description: "يثقون بنظامنا يومياً",
      color: "text-primary",
      bgColor: "bg-primary/10",
    },
    {
      icon: TrendingUp,
      value: "+2M",
      label: "معاملة مالية",
      description: "تمت معالجتها بنجاح",
      color: "text-secondary",
      bgColor: "bg-secondary/10",
    },
    {
      icon: Clock,
      value: "< 2 ثانية",
      label: "وقت المعالجة",
      description: "سرعة فائقة في الأداء",
      color: "text-amber-500",
      bgColor: "bg-amber-500/10",
    },
    {
      icon: Award,
      value: "99.9%",
      label: "دقة محاسبية",
      description: "موثوقية عالية",
      color: "text-success",
      bgColor: "bg-success/10",
    },
  ];

  return (
    <section id="stats" className="py-20 px-4 relative overflow-hidden">
      {/* Background Pattern */}
      <div className="absolute inset-0 bg-gradient-hero opacity-5" />
      
      <div className="container mx-auto relative z-10">
        {/* Section Header */}
        <div className="text-center max-w-3xl mx-auto mb-16 animate-scale-in">
          <div className="inline-flex items-center gap-2 px-4 py-2 bg-success/10 rounded-full text-sm font-medium text-success mb-4">
            إحصائيات مذهلة
          </div>
          <h2 className="text-4xl lg:text-5xl font-black mb-6">
            أرقام تتحدث عن
            <span className="text-gradient"> نجاحنا</span>
          </h2>
          <p className="text-xl text-muted-foreground">
            انضم إلى آلاف المستخدمين الراضين واستمتع بتجربة محاسبية فريدة
          </p>
        </div>

        {/* Stats Grid */}
        <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
          {stats.map((stat, index) => {
            const Icon = stat.icon;
            return (
              <div
                key={index}
                className="card-gradient rounded-2xl p-8 text-center border border-border hover-lift animate-scale-in bg-white/80 dark:bg-gradient-to-b dark:from-[#23272b] dark:to-[#181c20] dark:bg-[#181c20]/95 dark:border-[#2d3238] dark:shadow-2xl"
                style={{ animationDelay: `${index * 0.1}s` }}
              >
                <div className={`w-16 h-16 ${stat.bgColor} rounded-2xl flex items-center justify-center mx-auto mb-4`}
                  style={{ backgroundColor: undefined }}
                >
                  <Icon className={`w-8 h-8 ${stat.color}`} />
                </div>
                <div className="text-4xl font-black text-gradient mb-2">
                  {stat.value}
                </div>
                <div className="text-lg font-bold mb-2">
                  {stat.label}
                </div>
                <div className="text-sm text-muted-foreground">
                  {stat.description}
                </div>
              </div>
            );
          })}
        </div>

        {/* Trust Badges */}
        <div className="mt-16 flex flex-wrap justify-center items-center gap-8">
          <div className="text-center">
            <div className="text-sm text-muted-foreground mb-2">موثوق من قبل</div>
            <div className="flex gap-6 items-center">
              {[1, 2, 3, 4].map((i) => (
                <div
                  key={i}
                  className="w-24 h-12 bg-muted/50 rounded-lg flex items-center justify-center"
                >
                  <span className="text-xs font-semibold text-muted-foreground">
                    شركة {i}
                  </span>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

export default Stats;
