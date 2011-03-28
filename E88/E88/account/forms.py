# coding=utf-8
from django.contrib.auth.models import User
from django import forms
from django.utils.translation import ugettext_lazy as _
from django.template import Context, loader
from django.contrib.auth.tokens import default_token_generator
from django.contrib.sites.models import get_current_site
from django.utils.http import int_to_base36
from django.utils.safestring import mark_safe
from django.conf import settings
from django.forms.extras.widgets import SelectDateWidget
import datetime
from account.models import UserProfile
from home.models import Country, City, Province

class HorizRadioRenderer(forms.RadioSelect.renderer):
    """ this overrides widget method to put radio buttons horizontally
        instead of vertically.
    """
    def render(self):
        """Outputs radios"""
        return mark_safe(u'\n'.join([u'%s\n' % w for w in self]))
    
class UserRegisterForm(forms.ModelForm):
    """
    A form that creates a user, with no privileges, from the given username and password.
    """
    username = forms.RegexField(label=_("Username"), max_length=20, regex=r'^[\w]+$',help_text = _("Required. 20 characters or fewer. Letters, digits and _ only."), error_messages = {'invalid': _("This value may contain only letters, numbers and _ characters.")})
    password = forms.CharField(label=_("Password"), widget=forms.PasswordInput)
    password_confirm = forms.CharField(label=_("Password confirmation"), widget=forms.PasswordInput, help_text = _("Enter the same password as above, for verification."))
    email = forms.EmailField(label=_('Email'), max_length=50)
    agree = forms.BooleanField(label=_('Agree'))

    class Meta:
        model = User
        fields = ("username",)

    def clean_username(self):
        username = self.cleaned_data["username"]
        try:
            User.objects.get(username=username)
        except User.DoesNotExist:
            return username
        raise forms.ValidationError(_("A user with that username already exists."))
    
    def clean_email(self):
        email = self.cleaned_data['email']
        try:
            User.objects.get(email=email)
        except User.DoesNotExist:
            return email
        raise forms.ValidationError(_('A user with that email already exists.'))

    def clean_password_confirm(self):
        password1 = self.cleaned_data.get("password", "")
        password2 = self.cleaned_data["password_confirm"]
        if password1 != password2:
            raise forms.ValidationError(_("The two password fields didn't match."))
        return password2

    def save(self, commit=True, request=None):
        user = super(UserRegisterForm, self).save(commit=False)
        user.set_password(self.cleaned_data["password"])
        user.email = self.cleaned_data['email']
        if commit:
            user.save()
            UserProfile.objects.create(user=user, 
#                                       birthday = None,
#                                       gender = u'U',
#                                       phone = None,
#                                       address = None,
#                                       address2 = None,
#                                       city = None,
#                                       zip = None,
#                                       language = None,
#                                       province = None,
                                       lastip = request.META['REMOTE_ADDR'],
                                       registerip = request.META['REMOTE_ADDR'],
#                                       country = None,
#                                       available_balance = 0,
#                                       cash_balance = 0,
#                                       channel_balance = 0,
#                                       hold_balance = 0,
#                                       balance_update_time = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
                                       )
            if settings.REGISTER_VERIFY_EMAIL and request:
                from django.core.mail import send_mail
                current_site = get_current_site(request)
                site_name = current_site.name
                domain = current_site.domain
                t = loader.get_template('activation_email.html')
                c = {
                    'email': user.email,
                    'domain': domain,
                    'site_name': site_name,
                    'uid': int_to_base36(user.id),
                    'user': user,
                    'token': default_token_generator.make_token(user),
                    'protocol': request.is_secure() and 'https' or 'http',
                }
                send_mail(_("Account activation on %s") % site_name,
                    t.render(Context(c)), None, [user.email])

        return user

class UserFullnameForm(forms.ModelForm):
    first_name = forms.CharField(label=_('First name'), max_length=30)
    last_name = forms.CharField(label=_('Last name'), max_length=30)
    
    def save(self):
        u = super(UserFullnameForm, self).save(commit=False)
        return u;

    class Meta:
        model = User
        fields = ("first_name","last_name", "id")

class UserRegister2Form(forms.ModelForm):
    gender = forms.ChoiceField(label=_('Gender'), choices=((u'M', _('Male')),(u'F', _('Female')),(u'U', _('Unknown')),), widget=forms.RadioSelect(renderer=HorizRadioRenderer, attrs={'label_class':'plain'}))
    phone = forms.RegexField(label=_('Phone Number'), max_length=11, regex=r'[\d]{7,11}$')
    birthday = forms.DateField(label=_('Date of Birth'), widget=SelectDateWidget(years=range(1900, datetime.date.today().year-10), attrs={'style':'width:65px'}))
    address = forms.CharField(label=_('Street address'), max_length=100)
    address2 = forms.CharField(label=_('Apt/Suite number'), max_length=100, required=False)
    city = forms.ModelChoiceField(label=_('City'), queryset=City.objects.all(), widget=forms.Select(attrs={'disabled':'disabled'}))
    province = forms.ModelChoiceField(label=_('State/Province'), queryset=Province.objects.all(), widget=forms.Select(attrs={'disabled':'disabled'},choices=(('',_('Province')),)))
    country = forms.ModelChoiceField(label=_('Country'),queryset=Country.objects.all(), empty_label=_('Please choice your country'))
    zip = forms.RegexField(label=_('Zip'),max_length=6, regex=r'^[\d]{4,6}$')
    
    def __init__(self, *args, **kwargs):
        super(UserRegister2Form, self).__init__(*args, **kwargs)
        
               
    def save(self):
        profile = super(UserRegister2Form, self).save(commit=False)
        return profile
        
    class Meta:
        model = UserProfile
        exclude = ('user','lastip','registerip','available_balance','cash_balance','channel_balance','hold_balance','language')

class UserRegisterConfirmForm(forms.Form):
    promo_code = forms.CharField(label=_('Promotional Code'), min_length=20, max_length=20, required=False)
    
    def save(self, request=None):
        request.user.first_name = request.session['profile'].first_name
        request.user.last_name = request.session['profile'].last_name
        request.user.save()
        
        userProfile = UserProfile.objects.get(user=request.user)
        userProfile.gender = request.session['profile'].gender
        userProfile.phone = request.session['profile'].phone
        userProfile.birthday = request.session['profile'].birthday
        userProfile.address = request.session['profile'].address
        userProfile.address2 = request.session['profile'].address2
        userProfile.zip = request.session['profile'].zip
        userProfile.city = request.session['profile'].city
        userProfile.province = request.session['profile'].province
        userProfile.country = request.session['profile'].country
        
        userProfile.save()
        del(request.session['profile'])
        return request.user

    
class UserProfileForm(forms.ModelForm):
    class Meta:
        model = UserProfile
    pass

class UserRegConfirmForm(forms.Form):
    pass

class UserDepositForm(forms.Form):
    pass

class UserWithdrawForm(forms.Form):
    pass


