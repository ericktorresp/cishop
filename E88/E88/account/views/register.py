# coding=utf-8
from django.http import Http404, HttpResponse, HttpResponseRedirect
from django.template import RequestContext, Context, Template
from django.contrib.auth.models import User
from django.utils.translation import ugettext as _
from django.conf import settings

from django.contrib.auth.decorators import login_required
from django.utils.decorators import method_decorator
from django.views.decorators.csrf import csrf_protect
from django.views.decorators.cache import never_cache
from e8.home.views import BaseView
from django.views.generic.edit import CreateView, UpdateView
from e8.account.forms import *
from django.shortcuts import render_to_response

from django.core.urlresolvers import reverse
from django.utils.functional import lazy 

class RegisterView(CreateView):
    template_name = 'register.html'
    form_class = UserRegisterForm
    model = User
    success_url = lazy(reverse, str)("register2")
    
    def get_context_data(self, **kwargs):
        context = super(RegisterView, self).get_context_data(**kwargs)
        context['site'] = get_current_site(self.request)
        return context

    def get_form_kwargs(self, **kwargs):
        kwargs = super(RegisterView, self).get_form_kwargs(**kwargs)
        kwargs['request'] = self.request
        return kwargs
    
    def dispatch(self, request, *args, **kwargs):
        if request.user.is_authenticated():
            return HttpResponseRedirect(reverse('account_index', current_app='account'))
        return super(RegisterView, self).dispatch(request, *args, **kwargs)

#class RegisterStep2View(UpdateView):
#    template_name = 'register_step2.html'
#    
#    def get_object(self):
#        pass
#    def get_context_data(self, **kwargs):
#        context = super(RegisterStep2View, self).get_context_data(**kwargs)
#        context['site'] = get_current_site(self.request)
#        context['uform'] = UserFullnameForm()
#        context['pform'] = UserRegister2Form()
#        return context
@csrf_protect
@login_required
def register_step2(request):
    if request.method == 'POST':
        name_form = UserFullnameForm(data=request.POST, prefix="u")
        profile_form = UserRegister2Form(data=request.POST, prefix="p")
        if name_form.is_valid() and profile_form.is_valid():
            user = name_form.save()
            profile_form.cleaned_data["user"] = user
            profile = profile_form.save()
            request.session['profile'] = profile
            request.session['profile'].first_name = user.first_name
            request.session['profile'].last_name = user.last_name
            return HttpResponseRedirect(reverse('register_confirm'))
    else:
        name_form = UserFullnameForm(instance=request.user, prefix="u")
        try:
            user_profile = request.user.get_profile()
        except UserProfile.DoesNotExist:
            user_profile = UserProfile.objects.create(user=request.user,lastip = request.META['REMOTE_ADDR'],registerip = request.META['REMOTE_ADDR'])
        profile_form = UserRegister2Form(instance=user_profile, prefix="p")
        
    return render_to_response('register_step2.html', {'uform':name_form, 'pform':profile_form, 'site':get_current_site(request)}, context_instance=RequestContext(request))

@csrf_protect
@login_required
def register_confirm(request):
    if request.session.get('profile', None) is None:
        return HttpResponseRedirect(reverse('account_index'))
    if request.method == 'POST':
        form = UserRegisterConfirmForm(request.POST)
        if form.is_valid():
            profile = form.save(request=request)
            return HttpResponseRedirect(reverse('register_done'))
    else:
        form = UserRegisterConfirmForm()
        
    return render_to_response('register_confirm.html', {'form':form, 'site':get_current_site(request)}, context_instance=RequestContext(request))

def register_done(request):
    return render_to_response('register_done.html', {'site':get_current_site(request)}, context_instance=RequestContext(request))
