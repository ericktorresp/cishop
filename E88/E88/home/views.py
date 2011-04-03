# coding=utf-8

from django.shortcuts import render_to_response
from django.template import RequestContext, Context, Template
from django.http import Http404, HttpResponse, HttpResponseRedirect
from django.contrib.auth.models import User
from django.utils.translation import ugettext as _
from django.conf import settings
from django.views.generic import TemplateView
from django.contrib.sites.models import get_current_site
from django.core.urlresolvers import reverse

class BaseView(TemplateView):
    template_name = 'home/index.html'

    def get_context_data(self, **kwargs):
        context = super(BaseView, self).get_context_data(**kwargs)
        context['site'] = get_current_site(self.request)
        return context
    
    def dispatch(self, request, *args, **kwargs):
        if request.user.is_authenticated() and request.user.first_name == '':
            return HttpResponseRedirect(reverse('register2', current_app='home'))
        return super(BaseView, self).dispatch(request, *args, **kwargs)

class HomeView(BaseView):
    template_name = 'home/index.html'


from django.views.generic.edit import CreateView
from django.utils.functional import lazy 
class TestView(CreateView):
    template_name = 'register.html'
    model = User
    success_url = lazy(reverse, str)("register2") 
    
#def test(request):
#    return HttpResponse(reverse('home'))
