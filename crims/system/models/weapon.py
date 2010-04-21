from django.db import models
from filebrowser.fields import FileBrowseField
from django.contrib.auth.models import User
from django.utils.translation import ugettext_lazy, ugettext as _

WEAPON_CATS = (
    ('melee', _('Melee')),
    ('rifle', _('Rifle')),
    ('handgun', _('Handgun')),
    ('heavy', _('Heavy')),
)
class Weapon(models.Model):
    title = models.CharField(max_length=100)
    photo = FileBrowseField(u'Photo', max_length=200, directory="weapon/", format="Image", extensions=['.jpg', '.gif', '.png'])
    price = models.IntegerField()
    damage_min = models.SmallIntegerField()
    damage_max = models.SmallIntegerField()
    skill = models.SmallIntegerField()
    proficiency = models.SmallIntegerField()
    type = models.CharField(max_length=20, choices=WEAPON_CATS)
    durability = models.SmallIntegerField()
    created = models.DateTimeField('Created', editable=False, auto_now_add=True)
    user_weapon = models.ManyToManyField(User, through='UserWeapon')

    def damages(self):
        return "%s - %s" % (self.damage_min, self.damage_max)
    damages.short_description = _('Damage')
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        app_label = 'system'
        ordering = ['id', ]

class UserWeapon(models.Model):
    user = models.ForeignKey(User)
    weapon = models.ForeignKey(Weapon)
    actived = models.BooleanField(default=False)
    used = models.IntegerField(default=0, blank=True, null=True)
    created = models.DateTimeField('Created', editable=False, auto_now_add=True)
    modified = models.DateTimeField('Updated', editable=False, auto_now=True)
    
    def __unicode__(self):
        return self.user.username + "'s " + self.weapon.title

    class Meta:
        db_table = 'user_weapon'
        app_label = 'system'
