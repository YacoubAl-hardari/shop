import { Button } from "@/components/ui/button";
import { ThemeToggle } from "@/components/ui/theme-toggle";
import { Menu, X } from "lucide-react";
import { useState } from "react";

const Header = () => {
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

  const navItems = [
    { name: "الرئيسية", href: "/" },
    { name: "المميزات", href: "/#features" },
    { name: "الإحصائيات", href: "/#stats" },
    { name: "الشهادات", href: "/#testimonials" },
    { name: "الأسئلة", href: "/#faq" },
    { name: "خارطة الطريق", href: "/timeline" },
  ];

  return (
    <header className="fixed top-0 left-0 right-0 z-50 bg-background/80 backdrop-blur-lg border-b border-border">
      <nav className="container mx-auto px-4 py-4">
        <div className="flex items-center justify-between">
          {/* Logo */}
          <div className="flex items-center gap-2">
            <div className="w-10 h-10 rounded-lg bg-gradient-hero flex items-center justify-center">
              <span className="text-2xl font-bold text-white">ح</span>
            </div>
            <span className="text-xl font-bold text-foreground hidden sm:block">
              نظام الحسابات
            </span>
          </div>

          {/* Desktop Navigation */}
          <div className="hidden md:flex items-center gap-8">
            {navItems.map((item) => (
              <a
                key={item.name}
                href={item.href}
                className="text-sm font-medium text-muted-foreground hover:text-primary transition-colors"
              >
                {item.name}
              </a>
            ))}
          </div>

          {/* CTA Buttons + Theme Toggle */}
          <div className="hidden md:flex items-center gap-3">
            <ThemeToggle />
            <Button variant="ghost" size="sm" asChild>
              <a href="/admin/login">تسجيل الدخول</a>
            </Button>
            <Button size="sm" className="bg-gradient-hero hover:opacity-90" asChild>
              <a href="/admin/register?type=merchant">جرب مجاناً</a>
            </Button>
          </div>

          {/* Mobile Menu Button */}
          <button
            className="md:hidden p-2"
            onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
          >
            {mobileMenuOpen ? <X /> : <Menu />}
          </button>
        </div>

        {/* Mobile Menu */}
        {mobileMenuOpen && (
          <div className="md:hidden mt-4 pb-4 animate-scale-in">
            <div className="flex flex-col gap-4">
              {navItems.map((item) => (
                <a
                  key={item.name}
                  href={item.href}
                  className="text-sm font-medium text-muted-foreground hover:text-primary transition-colors"
                  onClick={() => setMobileMenuOpen(false)}
                >
                  {item.name}
                </a>
              ))}
              <div className="flex flex-col gap-2 pt-4 border-t border-border">
                <ThemeToggle />
                <Button variant="outline" size="sm" asChild>
                  <a href="/admin/login">تسجيل الدخول</a>
                </Button>
                <Button size="sm" className="bg-gradient-hero hover:opacity-90" asChild>
                  <a href="/admin/register?type=merchant">جرب مجاناً</a>
                </Button>
              </div>
            </div>
          </div>
        )}
      </nav>
    </header>
  );
};

export default Header;
