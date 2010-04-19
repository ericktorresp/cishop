from system.models import *
from django.contrib import admin

class DrugAdmin(admin.ModelAdmin):
    list_display = ('title', 'price', 'stamina','spirit')
    
class BuildingAdmin(admin.ModelAdmin):
    list_display = ('title','price','expend','output','drug')
    
class BusinessAdmin(admin.ModelAdmin):
    list_display = ('title','price','expend','type','max_vistors','limit')
    
class CharacterAdmin(admin.ModelAdmin):
    list_display = ('title','intelligence','strength','charisma','tolerance','avatar')

admin.site.register(Avatar)
admin.site.register(Armor)
admin.site.register(Character,CharacterAdmin)
admin.site.register(Drug,DrugAdmin)
admin.site.register(Building,BuildingAdmin)
admin.site.register(Hooker)
admin.site.register(Business,BusinessAdmin)
admin.site.register(Guard)
admin.site.register(Weapon)
admin.site.register(Province)
admin.site.register(Event)
