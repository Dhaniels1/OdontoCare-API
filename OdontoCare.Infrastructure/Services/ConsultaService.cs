
using Microsoft.EntityFrameworkCore;
using OdontoCare.Application.Common;
using OdontoCare.Application.Interfaces;
using OdontoCare.Domain.Entities;
using OdontoCare.Infrastructure.Data;
using System.Data;

namespace OdontoCare.Infrastructure.Services;

public class ConsultaService : IConsultaService
{
    private readonly OdontoCareDbContext _context;
    private readonly Microsoft.Extensions.Configuration.IConfiguration _config;

    public ConsultaService(OdontoCareDbContext context, Microsoft.Extensions.Configuration.IConfiguration config)
    {
        _context = context;
        _config = config;
    }

    public async Task<List<Consulta>> GetTodasAsync()
    {
        return await _context.Consultas
            .AsNoTracking()
            .ToListAsync();
    }

    public async Task<Consulta?> GetPorIdAsync(int id)
    {
        return await _context.Consultas
            .AsNoTracking()
            .FirstOrDefaultAsync(c => c.IdConsulta == id);
    }

    public async Task<Consulta> CriarAsync(Consulta consulta)
    {
        await ValidarRelacionamentosAsync(consulta);
        await ValidarDisponibilidadeAsync(consulta, null);

        _context.Consultas.Add(consulta);
        await _context.SaveChangesAsync();

        return consulta;
    }

    public async Task<bool> AtualizarAsync(int id, Consulta dados)
    {
        var consulta = await _context.Consultas
            .FirstOrDefaultAsync(c => c.IdConsulta == id);

        if (consulta == null)
            return false;

        await ValidarRelacionamentosAsync(dados);
        await ValidarDisponibilidadeAsync(dados, id);

        consulta.DataConsulta = dados.DataConsulta;
        consulta.HorarioConsulta = dados.HorarioConsulta;
        consulta.StatusConsulta = dados.StatusConsulta;
        consulta.Observacoes = dados.Observacoes;
        consulta.IdUsuario = dados.IdUsuario;
        consulta.IdPaciente = dados.IdPaciente;
        consulta.IdProcedimento = dados.IdProcedimento;

        await _context.SaveChangesAsync();

        return true;
    }

    public async Task<bool> ExcluirAsync(int id)
    {
        var consulta = await _context.Consultas
            .FirstOrDefaultAsync(c => c.IdConsulta == id);

        if (consulta == null)
            return false;

        consulta.StatusConsulta = "Cancelada";
        await _context.SaveChangesAsync();
        return true;
    }

    public Task<bool> CancelarAsync(int id) => ExcluirAsync(id);

    private async Task ValidarDisponibilidadeAsync(Consulta consulta, int? ignorarId)
    {
        if (consulta.DataConsulta == default || consulta.HorarioConsulta < TimeSpan.Zero || consulta.HorarioConsulta >= TimeSpan.FromDays(1))
            throw new BusinessException("Data ou horário inválido.");
        var inicioTexto = _config["Clinica:HorarioInicio"];
        var fimTexto = _config["Clinica:HorarioFim"];
        if (!TimeSpan.TryParse(inicioTexto, out var inicio) || !TimeSpan.TryParse(fimTexto, out var fim) || inicio >= fim)
            throw new BusinessException("Horário da clínica não configurado corretamente.");
        if (consulta.HorarioConsulta < inicio || consulta.HorarioConsulta >= fim)
            throw new BusinessException("Agendamento fora do horário de funcionamento configurado.");
        var existe = await _context.Consultas.AnyAsync(c =>
            (!ignorarId.HasValue || c.IdConsulta != ignorarId.Value) &&
            c.DataConsulta.Date == consulta.DataConsulta.Date &&
            c.HorarioConsulta == consulta.HorarioConsulta &&
            c.StatusConsulta != "Cancelada" &&
            (c.IdUsuario == consulta.IdUsuario || c.IdPaciente == consulta.IdPaciente));
        if (existe) throw new BusinessException("Paciente ou profissional já possui consulta neste horário.");
    }

    private async Task ValidarRelacionamentosAsync(Consulta consulta)
    {
        var pacienteExiste = await _context.Pacientes
            .AnyAsync(p => p.IdPaciente == consulta.IdPaciente);

        if (!pacienteExiste)
            throw new BusinessException(
                "O paciente informado não existe.");

        var usuarioExiste = await RegistroExisteAsync(
            "usuario", "idUsuario", consulta.IdUsuario);

        if (!usuarioExiste)
            throw new BusinessException(
                "O usuário informado não existe.");

        var procedimentoExiste = await RegistroExisteAsync(
            "procedimento", "idProcedimento", consulta.IdProcedimento);

        if (!procedimentoExiste)
            throw new BusinessException(
                "O procedimento informado não existe.");
    }

    private async Task<bool> RegistroExisteAsync(
        string tabela,
        string coluna,
        int id)
    {
        var conexao = _context.Database.GetDbConnection();
        var abriuConexao = conexao.State != ConnectionState.Open;

        if (abriuConexao)
            await conexao.OpenAsync();

        try
        {
            await using var comando = conexao.CreateCommand();

            comando.CommandText =
                $"SELECT COUNT(*) FROM `{tabela}` WHERE `{coluna}` = @id";

            var parametro = comando.CreateParameter();
            parametro.ParameterName = "@id";
            parametro.Value = id;
            comando.Parameters.Add(parametro);

            var resultado = await comando.ExecuteScalarAsync();

            return Convert.ToInt64(resultado) > 0;
        }
        finally
        {
            if (abriuConexao)
                await conexao.CloseAsync();
        }
    }
}
