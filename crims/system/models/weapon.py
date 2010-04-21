from django.db import models
from filebrowser.fields import FileBrowseField
from django.contrib.auth.models import User
from django.utils.translation import ugettext_lazy as _

WEAPON_CATS = (
    ('melee', _('Melee')),
    ('rifle', _('Rifle')),
    ('handgun', _('Handgun')),
    ('heavy', _('Heavy')),
)
class Weapon(models.Model):
    title = models.CharField(_('title'),max_length=100)
    photo = FileBrowseField(_('photo'), max_length=200, directory="weapon/", format="Image", extensions=['.jpg', '.gif', '.png'])
    price = models.IntegerField(_('price'))
    damage_min = models.SmallIntegerField(_('damage min'))
    damage_max = models.SmallIntegerField(_('damage max'))
    skill = models.SmallIntegerField(_('skill'))
    proficiency = models.SmallIntegerField(_('proficiency'))
    type = models.CharField(_('type'),max_length=20, choices=WEAPON_CATS)
    durability = models.SmallIntegerField(_('durability'))
    created = models.DateTimeField(_('created'), editable=False, auto_now_add=True)
    user_weapon = models.ManyToManyField(User, through='UserWeapon')

    def damages(self):
        return "%s - %s" % (self.damage_min, self.damage_max)
    damages.short_description = _('Damage')
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        app_label = 'system'
        ordering = ['id', ]
        verbose_name = _('Weapon')
        verbose_name_plural = _('Weapons')

class UserWeapon(models.Model):
    user = models.ForeignKey(User)
    weapon = models.ForeignKey(Weapon)
    actived = models.BooleanField(_('actived'),default=False)
    used = models.IntegerField(_('used'),default=0, blank=True, null=True)
    created = models.DateTimeField(_('created'), editable=False, auto_now_add=True)
    modified = models.DateTimeField(_('modified'), editable=False, auto_now=True)
    
    def __unicode__(self):
        return self.user.username + "'s " + self.weapon.title

    class Meta:
        db_table = 'user_weapon'
        app_label = 'system'
        verbose_name = _('User\'s Weapon')
        verbose_name_plural = _('User\'s Weapons')