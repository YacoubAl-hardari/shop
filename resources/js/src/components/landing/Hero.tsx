import { Button } from "@/components/ui/button";
import { ArrowLeft, CheckCircle2, Play, Zap } from "lucide-react";
import dashboardPreview from "@/assets/dashboard-preview.png";

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
              نظام متكامل لإدارة الحسابات المالية
            </div>

            <h1 className="text-5xl lg:text-6xl font-black leading-tight">
              أدر حسابات تجارك
              <br />
              <span className="text-gradient">بكل سهولة وذكاء</span>
            </h1>

            <p className="text-xl text-muted-foreground leading-relaxed max-w-xl">
              نظام محاسبي ذكي يوفر لك أدوات متقدمة لإدارة التجار، الطلبات، المدفوعات، 
              والتقارير المالية بدقة واحترافية عالية
            </p>

            {/* Features List */}
            <div className="grid sm:grid-cols-2 gap-4">
              {[
                "قيود محاسبية تلقائية",
                "تنبيهات ذكية للديون",
                "تقارير مالية شاملة",
                "واجهة سهلة الاستخدام",
              ].map((feature) => (
                <div key={feature} className="flex items-center gap-2">
                  <CheckCircle2 className="w-5 h-5 text-success flex-shrink-0" />
                  <span className="text-sm font-medium">{feature}</span>
                </div>
              ))}
            </div>

            {/* CTA Buttons */}
            <div className="flex flex-col sm:flex-row gap-4">
              <Button size="lg" className="bg-gradient-hero hover:opacity-90 text-lg px-8">
                ابدأ التجربة المجانية
                <ArrowLeft className="mr-2 w-5 h-5" />
              </Button>
              <Button size="lg" variant="outline" className="text-lg px-8">
                <Play className="ml-2 w-5 h-5" />
                شاهد الفيديو
              </Button>
            </div>

            {/* Stats */}
            <div className="flex flex-wrap gap-8 pt-4">
              <div>
                <div className="text-3xl font-bold text-gradient">+5000</div>
                <div className="text-sm text-muted-foreground">تاجر نشط</div>
              </div>
              <div>
                <div className="text-3xl font-bold text-gradient">+50K</div>
                <div className="text-sm text-muted-foreground">معاملة يومياً</div>
              </div>
              <div>
                <div className="text-3xl font-bold text-gradient">99.9%</div>
                <div className="text-sm text-muted-foreground">دقة محاسبية</div>
              </div>
            </div>
          </div>

          {/* Hero Image/Illustration */}
          <div className="relative lg:block animate-scale-in" style={{ animationDelay: "0.2s" }}>
            <div className="relative rounded-2xl bg-gradient-hero p-1 hover-glow">
              <div className="w-full rounded-xl bg-background/95 backdrop-blur-sm p-4 flex items-center justify-center overflow-hidden">
                <img 
                  src={dashboardPreview} 
                  alt="لوحة تحكم النظام - عرض توضيحي" 
                  className="w-full h-auto rounded-lg shadow-xl"
                />
              </div>
            </div>
            
            {/* Floating Stats Cards */}
            <div className="absolute -top-4 -right-4 bg-card rounded-xl shadow-xl p-4 border border-border animate-float">
              <div className="text-2xl font-bold text-success">↑ 24%</div>
              <div className="text-xs text-muted-foreground">زيادة الإيرادات</div>
            </div>
            
            <div className="absolute -bottom-4 -left-4 bg-card rounded-xl shadow-xl p-4 border border-border animate-float" style={{ animationDelay: "1s" }}>
              <div className="text-2xl font-bold text-primary flex items-center gap-1">
                <Zap className="w-6 h-6 inline-block align-middle" /> سريع
              </div>
              <div className="text-xs text-muted-foreground">معالجة فورية</div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

export default Hero;
