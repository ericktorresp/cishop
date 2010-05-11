from django.db import models
from django.contrib.auth.models import User
from django.utils.translation import ugettext_lazy as _

from game.models import UserBusiness

class UserFavorite(models.Model):
    user = models.ForeignKey(User, verbose_name=_('user'), related_name='favorite_user')
    user_business = models.ForeignKey(UserBusiness, verbose_name=_('business'), related_name='favorite_user_business')
    created = models.DateTimeField(_('created'), editable=False, auto_now_add=True)
    
    def __unicode__(self):
        return _('%(user)s\'s favorite: %(business)s') % {'user':self.user.username, 'business':self.user_business.title}
    
    class Meta:
        app_label = 'game'
        verbose_name = _('user favorited business')
        verbose_name_plural = _('user favorited businesses')
        unique_together = ('user', 'user_business')
