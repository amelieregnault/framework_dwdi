{% extends "base.mvc.php" %}

{% block title %}Products{% endblock %}
 
{% block body %} 


     <h1>Products ({{ total }})</h1>

     <a href="/Products/new">New Product</a>

     {% for product in products %}

         <h2>
             <a href="/products/{{ product.id }}/show">
                 {{ product.name }}
            </a>
         </h2>

     {% endfor %}

{% endblock %}

 </body>

 </html>