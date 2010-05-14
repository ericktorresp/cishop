from django.db import models
from system.models import Business, UserDrug
from django.contrib.auth.models import User
from django.utils.translation import ugettext_lazy as _

class UserBusiness(models.Model):
    user = models.ForeignKey(User, verbose_name = _('user'))
    business = models.ForeignKey(Business, verbose_name = _('business'))
    title = models.CharField(_('title'), max_length = 200, blank = True, null = True)
    description = models.CharField(_('description'), max_length = 255, blank = True, null = True)
    max_respect = models.SmallIntegerField(_('max_respect'))
    entrance_fee = models.SmallIntegerField(_('entrance_fee'))
    created = models.DateTimeField(_('created'), editable = False, auto_now_add = True)
    modified = models.DateTimeField(_('modified'), editable = False, auto_now = True)
    income = models.IntegerField(_('income'))
    closed = models.BooleanField(_('closed'), default = False)
    user_drug = models.ManyToManyField(UserDrug, verbose_name = _('drug'), through = 'UserBusinessDrug')

    def __unicode__(self):
        if self.title:
            return self.title
        else:
            return self.business.title

    def save(self):
        if not self.title:
            self.title = _("%(user)s's %(business)s") % {'user':self.user.username, 'business':self.business.title}
        super(UserBusiness, self).save()

    class Meta:
        verbose_name = _('user\'s business')
        verbose_name_plural = _('user\'s businesses')
        db_table = 'user_business'
        app_label = 'game'

class UserBusinessDrug(models.Model):
    userbusiness = models.ForeignKey(UserBusiness, verbose_name = _('user\'s business'))
    userdrug = models.ForeignKey(UserDrug, verbose_name = _("user's drug"))
    price = models.SmallIntegerField(_('price'))
    sold = models.SmallIntegerField(_('sold'))
    removed = models.BooleanField(_('removed'), default = False)

    def __unicode__(self):
        return self.userdrug.title

    class Meta:
        app_label = 'game'
        verbose_name = _('user business drug')
        verbose_name = _('user business drugs')
        db_table = 'user_business_user_drug'


