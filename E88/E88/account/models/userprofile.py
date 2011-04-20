from django.db import models
from django.contrib.auth.models import User
from django.utils.translation import ugettext_lazy as _
from django.conf import settings
from home.models import Province, Country, City
from bank.models import Bank

GENDER = (('M', _('male')), ('F', _('female')), ('U', _('unknown')))

class UserProfile(models.Model):
    user = models.ForeignKey(User, unique=True)
    birthday = models.DateField(_('birthday'), blank=True, null=True)
    gender = models.CharField(_('gender'), max_length=1, choices=GENDER, default='U')
    phone = models.CharField(_('phone'), max_length=11, null=True, blank=True)
    mobile = models.CharField(_('mobile'), max_length=15, blank=True)
    address = models.CharField(_('street address'), max_length=255, null=True, blank=True)
    address2 = models.CharField(_('apt/suite number'), max_length=255, null=True, blank=True)
    city = models.ForeignKey(City, verbose_name = _('city'), null=True, blank=True)
    zip = models.CharField(_('zip'), max_length=8, null=True, blank=True)
    language = models.CharField(_('language'), choices=settings.LANGUAGES, max_length=10, null=True, blank=True)
    province = models.ForeignKey(Province, verbose_name=_('province'), null=True, blank=True)
    lastip = models.IPAddressField(_('last IP'), null=True, blank=True)
    registerip = models.IPAddressField(_('register IP'), null=True, blank=True)
    country = models.ForeignKey(Country, verbose_name=_('country'), to_field="iso", null=True, blank=True)
    available_balance = models.DecimalField(_('available balance'), max_digits=14, decimal_places=4, default=0)
    cash_balance = models.DecimalField(_('cash balance'), max_digits=14, decimal_places=4, default=0)    
    channel_balance = models.DecimalField(_('channel balance'), max_digits=14, decimal_places=4, default=0)
    hold_balance = models.DecimalField(_('hold balance'), max_digits=14, decimal_places=4, default=0)
    balance_update_time = models.DateTimeField(_('balance last update'), null=True, editable=False)
    email_verified = models.BooleanField(_('email verified'), default=False)
    security_password = models.CharField(_('security password'), max_length=128, blank=True, null=True)
        
    def __unicode__(self):
        return _('%s\'s profile') % self.user.username

    def delete(self):
        if self.user:
            return False
        super(UserProfile, self).delete()
    
    class Meta:
        app_label = 'account'
        verbose_name = _('profile')
        verbose_name_plural = _('profiles')
        db_table = 'user_profile'

    class Admin:
        list_display = ('gender',)

User.profile = property(lambda u: UserProfile.objects.get_or_create(user=u)[0])
