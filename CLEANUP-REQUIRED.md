# Cleanup Required

⚠️ **Manual cleanup needed for the following old folders:**

These folders contain old 1.x code and should be deleted manually:

```
/api/              # Old API folder (now in src/)
/core/             # Old core folder (now in src/core/)
/integrations/     # Old integrations folder (now in src/integrations/)
/modules/          # Old modules folder (now in src/)
/templates/        # Old templates folder (now in src/templates/)
```

## How to Clean Up

### Option 1: Via Git Command Line
```bash
git rm -r api/ core/ integrations/ modules/ templates/
git commit -m "cleanup: remove old 1.x folders"
git push origin refactor/2.0.0-clean
```

### Option 2: Via GitHub Web Interface
Delete these folders manually through GitHub's web interface.

---

**After cleanup, this file should also be deleted.**
