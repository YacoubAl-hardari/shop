import { Button } from "@/components/ui/button";
import { ArrowLeft, CheckCircle2, Sparkles, Rocket, CreditCard, Lock } from "lucide-react";

const CTA = () => {

  return (
    <section className="py-20 px-4 relative overflow-hidden">
      {/* Background */}
      <div className="absolute inset-0 bg-gradient-hero opacity-10" />
      <div className="absolute top-1/2 left-1/4 w-96 h-96 bg-primary/20 rounded-full blur-3xl animate-float" />
      <div className="absolute top-1/2 right-1/4 w-96 h-96 bg-secondary/20 rounded-full blur-3xl animate-float" style={{ animationDelay: "2s" }} />

      <div className="container mx-auto relative z-10">
        <div className="max-w-4xl mx-auto">
          {/* Main CTA Card */}
          <div className="card-gradient rounded-3xl p-8 md:p-12 border border-border shadow-sm dark:shadow-md animate-scale-in text-center bg-white/80 dark:bg-gradient-to-b dark:from-[#23272b] dark:to-[#181c20] dark:bg-[#181c20]/95 dark:border-[#2d3238]">
            {/* Badge */}
            <div className="inline-flex items-center gap-2 px-4 py-2 bg-primary/10 rounded-full text-sm font-medium text-primary mb-6">
              <Sparkles className="w-4 h-4" />
              عرض خاص لفترة محدودة
            </div>

            {/* Heading */}
            <h2 className="text-4xl lg:text-5xl font-black mb-6">
              ابدأ رحلتك نحو
              <br />
              <span className="text-gradient">إدارة مالية احترافية</span>
            </h2>

            <p className="text-xl text-muted-foreground mb-8 max-w-2xl mx-auto">
              جرب النظام مجاناً لمدة 30 يوماً بدون الحاجة لبطاقة ائتمانية
              واستمتع بجميع المميزات
            </p>

            {/* Benefits List */}
            <div className="flex flex-wrap justify-center gap-6 mb-8">
              {[
                "تجربة مجانية 30 يوم",
                "بدون بطاقة ائتمانية",
                "دعم فني مجاني",
                "تدريب شامل",
              ].map((benefit) => (
                <div key={benefit} className="flex items-center gap-2">
                  <CheckCircle2 className="w-5 h-5 text-success" />
                  <span className="text-sm font-medium">{benefit}</span>
                </div>
              ))}
            </div>

            {/* Email Form */}
            <div className="flex flex-col sm:flex-row gap-3 max-w-md mx-auto mb-6 justify-center">
              <Button
                size="lg"
                variant="outline"
                className="h-12 px-8"
                asChild
              >
                <a href="/admin/login">تسجيل الدخول</a>
              </Button>
              <Button
                size="lg"
                className="bg-gradient-hero hover:opacity-90 h-12 px-8"
                asChild
              >
                <a href="/admin/register?type=merchant">
                  ابدأ كتاجر
                  <ArrowLeft className="mr-2 w-5 h-5" />
                </a>
              </Button>
            </div>

            <p className="text-xs text-muted-foreground">
              بالتسجيل، أنت توافق على{" "}
              <a href="#" className="underline hover:text-primary">
                شروط الخدمة
              </a>{" "}
              و
              <a href="#" className="underline hover:text-primary">
                {" "}
                سياسة الخصوصية
              </a>
            </p>
          </div>

          {/* Trust Indicators */}
          <div className="grid md:grid-cols-3 gap-6 mt-12">
            {/* Card 1: إعداد سريع */}
            <div className="card-gradient rounded-2xl p-6 text-center border border-border animate-scale-in bg-white/80 dark:bg-gradient-to-b dark:from-[#23272b] dark:to-[#181c20] dark:bg-[#181c20]/95 dark:border-[#2d3238] shadow-xs dark:shadow-sm" style={{ animationDelay: `0.1s` }}>
              <div className="flex justify-center mb-3">
                <Rocket className="text-primary w-9 h-9" />
              </div>
              <h3 className="text-lg font-bold mb-2">إعداد سريع</h3>
              <p className="text-sm text-muted-foreground">ابدأ العمل في أقل من 5 دقائق</p>
            </div>
            {/* Card 2: بدون التزام */}
            <div className="card-gradient rounded-2xl p-6 text-center border border-border animate-scale-in bg-white/80 dark:bg-gradient-to-b dark:from-[#23272b] dark:to-[#181c20] dark:bg-[#181c20]/95 dark:border-[#2d3238] shadow-xs dark:shadow-sm" style={{ animationDelay: `0.2s` }}>
              <div className="flex justify-center mb-3">
                <CreditCard className="text-success w-9 h-9" />
              </div>
              <h3 className="text-lg font-bold mb-2">بدون التزام</h3>
              <p className="text-sm text-muted-foreground">إلغاء في أي وقت بدون رسوم</p>
            </div>
            {/* Card 3: آمن 100% */}
            <div className="card-gradient rounded-2xl p-6 text-center border border-border animate-scale-in bg-white/80 dark:bg-gradient-to-b dark:from-[#23272b] dark:to-[#181c20] dark:bg-[#181c20]/95 dark:border-[#2d3238] shadow-xs dark:shadow-sm" style={{ animationDelay: `0.3s` }}>
              <div className="flex justify-center mb-3">
                <Lock className="text-secondary w-9 h-9" />
              </div>
              <h3 className="text-lg font-bold mb-2">آمن 100%</h3>
              <p className="text-sm text-muted-foreground">بياناتك محمية بأعلى معايير الأمان</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

export default CTA;
