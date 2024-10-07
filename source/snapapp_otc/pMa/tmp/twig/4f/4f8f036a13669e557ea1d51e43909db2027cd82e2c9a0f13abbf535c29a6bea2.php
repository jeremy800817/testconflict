<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* navigation/tree/state.twig */
class __TwigTemplate_5eee0ca317f6fbc4d58fdd6df726dc3b0541374d91c7e55fc3876f644e37c1af extends \Twig\Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 1
        echo ($context["quick_warp"] ?? null);
        echo "

<div class=\"clearfloat\"></div>

<ul>
  ";
        // line 6
        echo ($context["fast_filter"] ?? null);
        echo "
  ";
        // line 7
        echo ($context["controls"] ?? null);
        echo "
</ul>

";
        // line 10
        echo ($context["page_selector"] ?? null);
        echo "

<div id='pma_navigation_tree_content'>
  <ul>
    ";
        // line 14
        echo ($context["nodes"] ?? null);
        echo "
  </ul>
</div>
";
    }

    public function getTemplateName()
    {
        return "navigation/tree/state.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  62 => 14,  55 => 10,  49 => 7,  45 => 6,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "navigation/tree/state.twig", "/usr/local/nginx/html/gtp/source/snapapp_otc/pMa/templates/navigation/tree/state.twig");
    }
}
