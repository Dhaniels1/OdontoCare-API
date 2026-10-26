using System.Security.Cryptography;
using OdontoCare.Application.Interfaces;

namespace OdontoCare.Infrastructure.Security;

public sealed class Pbkdf2PasswordService : IPasswordService
{
    private const int Iterations = 210_000;
    public string Hash(string senha)
    {
        var salt = RandomNumberGenerator.GetBytes(16);
        var bytes = Rfc2898DeriveBytes.Pbkdf2(senha, salt, Iterations, HashAlgorithmName.SHA256, 32);
        return $"pbkdf2-sha256${Iterations}${Convert.ToBase64String(salt)}${Convert.ToBase64String(bytes)}";
    }

    public bool Verify(string senha, string hash)
    {
        // Credenciais legadas em texto puro são rejeitadas até serem migradas.
        if (string.IsNullOrEmpty(hash)) return false;
        var campos = hash.Split('$');
        if (campos.Length != 4 || campos[0] != "pbkdf2-sha256" ||
            !int.TryParse(campos[1], out var iter) || iter is < 100_000 or > 1_000_000)
            return false;
        try
        {
            var salt = Convert.FromBase64String(campos[2]);
            var esperado = Convert.FromBase64String(campos[3]);
            if (salt.Length != 16 || esperado.Length != 32) return false;
            var derivado = Rfc2898DeriveBytes.Pbkdf2(senha, salt, iter, HashAlgorithmName.SHA256, esperado.Length);
            return CryptographicOperations.FixedTimeEquals(derivado, esperado);
        }
        catch (FormatException) { return false; }
    }
}
