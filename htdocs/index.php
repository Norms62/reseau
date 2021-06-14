<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>{% block title %}Réseau oups{% endblock %}</title>
        {% block stylesheets %}{{ encore_entry_link_tags('app') }}{% endblock %}
    </head>
    <body>
    <nav  style ="height:100px ; background-color: #59C3B5" class="navbar navbar-expand-lg">
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            {% if app.user %}
            <ul class="navbar-nav mr-auto">
            <li class="nav-item">
                <a style="color:white ; font-size : 25px ; padding : 25px" class="nav-link" href="{{path('ticket')}}">Tickets</a>
            </li>
            <li class="nav-item">
                <a style="color:white ; font-size : 25px ; padding : 25px" class="nav-link" href="{{path('ticket_assigne')}}">Mes tickets</a>
            </li>
            <li class="nav-item">
                <a style="color:white ; font-size : 25px ;padding : 25px" class="nav-link" href="{{path('ajoutTicket')}}">Ajouter un ticket</a>
            </li>
            <li class="nav-item">
                <a style="color:white ; font-size : 25px ; padding : 25px" class="nav-link" href="{{path('import')}}">Import fichier </a>
            </li>
            <li class="nav-item">
                <a style="color:white ; font-size : 25px ; padding : 25px" class="nav-link" href="{{path('statistiques')}}"> Statistiques </a>
            </li>
            {% if is_granted('ROLE_ADMIN') %}
            <li class="nav-item">
                <a style="color:white ; font-size : 25px ; padding : 25px" class="nav-link" href="{{path('admin')}}">Page Admin </a>
            </li>
            {% endif %}
            </ul>
            {% endif %}
            <ul style="position:absolute ; right:0" class="navbar-nav mr-auto">
            {% if app.user %}
            <li class="nav-item ">
                <a style="color:white ; font-size : 25px ; padding : 25px " class="nav-link" href="{{path('contact')}}">Contact</a>
            </li>
            {% endif %}
            {% if not app.user %}
            <li class="nav-item">
                <a style="color:white ; font-size : 25px ; padding : 25px" class="nav-link" href="{{path('connexion')}}">Connexion</a>
            </li>
            {% else %}
            <li class="nav-item ">
                <a style="color:white ; font-size : 25px ; padding : 25px " class="nav-link" href="{{path('deconnexion')}}">Deconnexion</a>
            </li>
            {% endif %}
            </ul>
        </div>
    </nav>
        {% block body %}{% endblock %}
        {% block javascripts %}{% endblock %}
    </body>
</html>
