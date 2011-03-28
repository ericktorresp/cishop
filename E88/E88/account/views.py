# coding=utf-8

from django.shortcuts import render_to_response
from django.template import RequestContext, Context, Template
from django.http import Http404, HttpResponse, HttpResponseRedirect
from django.contrib.auth.models import User
from django.utils.translation import ugettext as _

from django.contrib.auth.decorators import login_required
from django.views.decorators.csrf import csrf_protect
from django.views.decorators.cache import never_cache
from django.contrib.auth.tokens import default_token_generator
from django.utils.http import urlquote, base36_to_int

#from django import forms
from account.forms import *
from django.contrib.sites.models import get_current_site
try:
    import json
except:
    import simplejson as json

@login_required
def account(request):
    return render_to_response('index.html', {}, context_instance=RequestContext(request))

@csrf_protect
def register(request):
    if request.method == 'POST':
        form = UserRegisterForm(request.POST)
        if form.is_valid():
            form.save(request=request)
            from django.contrib import auth
            user = auth.authenticate(username=form.cleaned_data['username'], password=form.cleaned_data['password'])
            auth.login(request, user)
            user.get_profile()
            return HttpResponseRedirect("/account/register/step2")
    else:
        form = UserRegisterForm()

    return render_to_response("register.html", {'form': form, 'site':get_current_site(request)}, context_instance=RequestContext(request))

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
            return HttpResponseRedirect("/account/register/confirm")
    else:
        name_form = UserFullnameForm(instance=request.user, prefix="u")
        profile_form = UserRegister2Form(instance=request.user.get_profile(), prefix="p")
        
    return render_to_response('register_step2.html', {'uform':name_form, 'pform':profile_form, 'site':get_current_site(request)}, context_instance=RequestContext(request))

@csrf_protect
@login_required
def register_confirm(request):
    if request.method == 'POST':
        form = UserRegisterConfirmForm(request.POST)
        if form.is_valid():
            profile = form.save(request=request)
            return HttpResponseRedirect("/account/register/done")
    else:
        form = UserRegisterConfirmForm()
        
    return render_to_response('register_confirm.html', {'form':form, 'site':get_current_site(request)}, context_instance=RequestContext(request))

def register_done(request):
    return render_to_response('register_done.html', {'site':get_current_site(request)}, context_instance=RequestContext(request))

@login_required
def verify(request):
    return HttpResponse('verify page.')

@never_cache
def active(request, uidb36=None, token=None, template_name='activation.html',
                           token_generator=default_token_generator,
                           post_reset_redirect=None):
    """
    View that checks the hash in a password reset link and presents a
    form for entering a new password.
    """
    assert uidb36 is not None and token is not None # checked by URLconf
    if post_reset_redirect is None:
        post_reset_redirect = '/'
    try:
        uid_int = base36_to_int(uidb36)
        user = User.objects.get(id=uid_int)
    except (ValueError, User.DoesNotExist):
        user = None

    context_instance = RequestContext(request)

    if user is not None and token_generator.check_token(user, token):
        context_instance['account'] = user
        user.is_active = True
        user.save()

    return render_to_response(template_name, context_instance=context_instance)


@csrf_protect
@login_required
def profile(request):
    if request.method == "POST":
        form = UserProfileForm(request.POST)
        if form.is_valid():
            new_profile = form.save()
            return HttpResponseRedirect('/account/confirm')
    else:
        form = UserProfileForm()
        
    return render_to_response('profile.html', {'form': form, 'site':get_current_site(request)}, context_instance=RequestContext(request))

@csrf_protect
@login_required
def confirm(request):
    if request.method == 'POST':
        form = UserRegConfirmForm(request.POST)
        if form.is_valid():
            return HttpResponseRedirect('/account')
    else:
        form = UserRegConfirmForm()
        
    return render_to_response('confirm.html', {'form': form, 'site':get_current_site(request)}, context_instance=RequestContext(request))

@login_required    
def deposit(request):
    if request.method == 'POST':
        form = UserDepositForm(request.POST)
        if form.is_valid():
            new_deposit = form.save()
            return HttpResponseRedirect('/account/banking')
    else:
        form = UserDepositForm()
        
    return render_to_response('deposit.html', {'form':form, 'site':get_current_site(request)}, context_instance=RequestContext(request))

@csrf_protect
@login_required
def withdraw(request):
    if request.method == 'POST':
        form = UserWithdrawForm(request.POST)
        if form.is_valid():
            new_withdraw = form.save()
            return HttpResponseRedirect('/account/withdraw')
    else:
        form = UserWithdrawForm()
        
    return render_to_response('withdraw.html', {'form':form, 'site':get_current_site(request)}, context_instance=RequestContext(request))

@csrf_protect
@login_required
def referral(request):
    pass

@csrf_protect
@login_required
def password(request):
    pass
