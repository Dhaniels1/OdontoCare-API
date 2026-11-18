

namespace OdontoCare.Application.Common.Interfaces;

public interface IPasswordService
{
    string Hash(string senha);
    bool Verify(string senha, string hash);
}
