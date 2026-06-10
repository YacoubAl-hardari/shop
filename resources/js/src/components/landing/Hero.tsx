import { Button } from "@/components/ui/button";
import { ArrowLeft, CheckCircle2, Store, Users } from "lucide-react";

const Hero = () => {
  return (
    <section id="hero" className="relative pt-32 pb-20 px-4 overflow-hidden">
      {/* Background Gradients */}
      <div className="absolute top-0 left-1/4 w-96 h-96 bg-primary/20 rounded-full blur-3xl animate-float" />
      <div className="absolute bottom-0 right-1/4 w-96 h-96 bg-secondary/20 rounded-full blur-3xl animate-float" style={{ animationDelay: "2s" }} />

      <div className="container mx-auto relative z-10">
        <div className="grid lg:grid-cols-2 gap-12 items-center">
          {/* Content */}
          <div className="space-y-8 animate-scale-in">
            <div className="inline-flex items-center gap-2 px-4 py-2 bg-accent rounded-full text-sm font-medium text-accent-foreground">
              <span className="w-2 h-2 bg-success rounded-full animate-pulse" />
              نظام مالي ومحاسبي للتجار والعملاء
            </div>

            <h1 className="text-5xl lg:text-6xl font-black leading-tight">
              نظام متكامل
              <br />
              <span className="text-gradient">للتاجر والعميل</span>
            </h1>

            <p className="text-xl text-muted-foreground leading-relaxed max-w-xl">
              منصة واحدة تمكّن التاجر من إدارة مبيعاته ومحاسبته ومخزونه،
              وتمكّن العميل من متابعة حساباته ومديونياته لدى تجاره بكل شفافية.
            </p>

            {/* Features List — two columns, one for merchant, one for customer */}
            <div className="grid sm:grid-cols-2 gap-3">
              {/* Merchant features */}
              <div className="space-y-2">
                <div className="flex items-center gap-2 text-xs font-bold text-amber-600 dark:text-amber-400 mb-1">
                  <Store className="w-4 h-4" /> للتاجر
                </div>
                {[
                  "نقطة البيع (POS) بالباركود",
                  "فواتير حرارية احترافية",
                  "قيود محاسبية تلقائية",
                  "تتبع المخزون لحظياً",
                ].map((f) => (
                  <div key={f} className="flex items-center gap-2">
                    <CheckCircle2 className="w-4 h-4 text-amber-500 flex-shrink-0" />
                    <span className="text-sm font-medium">{f}</span>
                  </div>
                ))}
              </div>
              {/* Customer features */}
              <div className="space-y-2">
                <div className="flex items-center gap-2 text-xs font-bold text-blue-600 dark:text-blue-400 mb-1">
                  <Users className="w-4 h-4" /> للعميل
                </div>
                {[
                  "متابعة المديونيات والأرصدة",
                  "كشوف الحساب الفورية",
                  "مقارنة الإنفاق بين التجار",
                  "ميزانية شخصية ذكية",
                ].map((f) => (
                  <div key={f} className="flex items-center gap-2">
                    <CheckCircle2 className="w-4 h-4 text-blue-500 flex-shrink-0" />
                    <span className="text-sm font-medium">{f}</span>
                  </div>
                ))}
              </div>
            </div>

            {/* CTA Buttons */}
            <div className="flex flex-col sm:flex-row gap-4">
              <Button size="lg" className="bg-gradient-hero hover:opacity-90 text-lg px-8" asChild>
                <a href="/admin/register?type=merchant">
                  ابدأ كتاجر مجاناً
                  <ArrowLeft className="mr-2 w-5 h-5" />
                </a>
              </Button>
              <Button size="lg" variant="outline" className="text-lg px-8" asChild>
                <a href="/admin/login">تسجيل الدخول</a>
              </Button>
            </div>

            {/* Role Badges */}
            <div className="flex flex-wrap gap-6 pt-2">
              <div className="flex items-center gap-2 px-4 py-2 rounded-xl bg-amber-500/10 border border-amber-500/20">
                <Store className="w-5 h-5 text-amber-500" />
                <div>
                  <div className="text-sm font-bold">حساب تاجر</div>
                  <div className="text-xs text-muted-foreground">POS + محاسبة + مخزون</div>
                </div>
              </div>
              <div className="flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-500/10 border border-blue-500/20">
                <Users className="w-5 h-5 text-blue-500" />
                <div>
                  <div className="text-sm font-bold">حساب عميل</div>
                  <div className="text-xs text-muted-foreground">ديون + أرصدة + مقارنة</div>
                </div>
              </div>
            </div>
          </div>

          {/* Hero Image — real system screenshots */}
          <div className="relative lg:block animate-scale-in" style={{ animationDelay: "0.2s" }}>
            <div className="relative rounded-2xl bg-gradient-hero p-1 hover-glow">
              <div className="w-full rounded-xl bg-background/95 backdrop-blur-sm overflow-hidden">
                <img
                  src="/merchant/لوحة الإحصائيات.png"
                  alt="لوحة إحصائيات النظام"
                  className="w-full h-auto rounded-lg shadow-xl"
                />
              </div>
            </div>

            {/* Floating Badge — POS */}
            <div className="absolute -top-4 -right-4 bg-card rounded-xl shadow-xl p-3 border border-border animate-float">
              <div className="text-sm font-bold text-amber-500">🏪 POS</div>
              <div className="text-xs text-muted-foreground">فاتورة في 3 ثوانٍ</div>
            </div>

            {/* Floating Badge — Customer */}
            <div className="absolute -bottom-4 -left-4 bg-card rounded-xl shadow-xl p-3 border border-border animate-float" style={{ animationDelay: "1s" }}>
              <div className="text-sm font-bold text-blue-500">👤 عميل</div>
              <div className="text-xs text-muted-foreground">متابعة الديون فورياً</div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

export default Hero;
