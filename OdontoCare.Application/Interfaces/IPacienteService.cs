
using OdontoCare.Domain.Entities;

namespace OdontoCare.Application.Interfaces;

public interface IPacienteService
{
    Task<List<Paciente>> GetTodosAsync();

    Task<Paciente?> GetPorIdAsync(int id);

    Task<Paciente> CriarAsync(Paciente paciente);

    Task<bool> AtualizarAsync(int id, Paciente paciente);

    Task<bool> ExcluirAsync(int id);

    Task<bool> CpfExisteAsync(string cpf);
}
