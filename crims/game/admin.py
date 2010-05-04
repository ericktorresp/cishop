from game.models import *
from system.models import *
from django.contrib import admin
from django.contrib.auth.admin import UserAdmin

class MemberInline(admin.StackedInline):
    model = GangMember
    extra = 3
    raw_id_fields = ['user']
    classes = ('collapse-closed',)

class NewsInline(admin.StackedInline):
    model = GangNews
    fk_name = 'gang'
    can_delete = False
    classes = ('collapse-closed',)
    allow_add = True

class InviteInline(admin.StackedInline):
    model = GangInvite
    classes = ('collapse-closed',)
    
class GangAdmin(admin.ModelAdmin):
    inlines = (MemberInline, NewsInline, InviteInline,)
    raw_id_fields = ['creater', 'leader', 'vice_leader', ]
    list_display = ('title', 'creater', 'leader', 'vice_leader', 'created',)

class BountyAdmin(admin.ModelAdmin):
    raw_id_fields = ['sponsor', 'target']
    list_display = ('sponsor', 'target', 'credits', 'expired', 'completed',)

class BankInline(admin.StackedInline):
    model = Bank
    fk_name = 'user'
    can_delete = False
    classes = ('collapse-closed',)

class ArmorInline(admin.StackedInline):
    model = UserArmor
    fk_name = 'user'
    can_delete = False
    classes = ('collapse-closed',)
    allow_add = True

class WeaponInline(admin.StackedInline):
    model = UserWeapon
    fk_name = 'user'
    can_delete = False
    classes = ('collapse-closed',)
    allow_add = True
    
class GuardInline(admin.StackedInline):
    model = UserGuard
    fk_name = 'user'
    can_delete = False
    classes = ('collapse-closed',)
    allow_add = True
        
class BankAdmin(UserAdmin):
    inlines = (BankInline, ArmorInline, WeaponInline, GuardInline,)
        
admin.site.register(Gang, GangAdmin)
admin.site.register(Bounty, BountyAdmin)
admin.site.unregister(User)
admin.site.register(User, BankAdmin)
